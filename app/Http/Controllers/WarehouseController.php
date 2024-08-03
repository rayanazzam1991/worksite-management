<?php

namespace App\Http\Controllers;

use App\Exceptions\InValidWarehouseItemMoveException;
use App\Helpers\ApiResponse\ApiResponseHelper;
use App\Helpers\ApiResponse\ErrorResult;
use App\Helpers\ApiResponse\Result;
use App\Http\Requests\WarehouseCreateRequest;
use App\Http\Requests\WarehouseItemsAddRequest;
use App\Http\Requests\WarehouseItemsListRequest;
use App\Http\Requests\WarehouseItemsMoveItemsRequest;
use App\Http\Requests\WarehouseItemUpdateItemsRequest;
use App\Http\Requests\WarehouseUpdateRequest;
use App\Http\Resources\WarehouseDetailsResource;
use App\Http\Resources\WarehouseItemsListResource;
use App\Http\Resources\WarehouseListResource;
use App\Models\Warehouse;
use App\Models\WarehouseItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class WarehouseController extends Controller
{
    public function list(): JsonResponse
    {
        $warehouses = Warehouse::query()->get();

        return ApiResponseHelper::sendSuccessResponse(new Result(WarehouseListResource::collection($warehouses)));
    }

    public function store(WarehouseCreateRequest $request): JsonResponse
    {
        /**
         * @var array{
         *     name:string,
         *     address_id:int|null
         * } $requestedData
         */
        $requestedData = $request->validated();
        Warehouse::query()->create($requestedData);

        return ApiResponseHelper::sendSuccessResponse();

    }

    public function update(int $warehouseId, WarehouseUpdateRequest $request): JsonResponse
    {
        /**
         * @var array{
         *     name:string|null,
         *     address_id:int|null
         * } $requestedData
         */
        $requestedData = $request->validated();

        $warehouse = Warehouse::query()->findOrFail($warehouseId);

        $warehouse->update($requestedData);

        return ApiResponseHelper::sendSuccessResponse();

    }

    public function show(int $warehouseId): JsonResponse
    {
        $warehouse = Warehouse::query()->findOrFail($warehouseId);

        return ApiResponseHelper::sendSuccessResponse(new Result(WarehouseDetailsResource::make($warehouse)));
    }

    public function destroy(int $warehouseId): JsonResponse
    {
        Warehouse::query()->findOrFail($warehouseId)->delete();

        return ApiResponseHelper::sendSuccessResponse();
    }

    /**
     * @throws Throwable
     */
    public function addItems(int $warehouseId, WarehouseItemsAddRequest $request): JsonResponse
    {
        /**
         * @var array{
         *     items: array<string,array{
         *      item_id:int,
         *      quantity:float,
         *      price:float|null
         *     }>,
         *     supplier_id:int|null,
         *     date:string|null
         * } $requestedData
         */
        $requestedData = $request->validated();

        $dataToSave = array_map(function ($item) use ($requestedData, $warehouseId) {
            return [
                'warehouse_id' => $warehouseId,
                'supplier_id' => $requestedData['supplier_id'],
                'date' => $requestedData['date'],
                'item_id' => $item['item_id'],
                'quantity' => $item['quantity'],
                'price' => $item['price'] ?? null,
            ];
        }, $requestedData['items']);
        try {
            DB::transaction(
                callback: function () use ($dataToSave) {
                    WarehouseItem::query()->insert($dataToSave);
                },
                attempts: 3);
        } catch (QueryException $exception) {
            if ($exception->getCode() == 23000) {
                return ApiResponseHelper::sendErrorResponse(new ErrorResult('Item already exists in this warehouse', Response::HTTP_CONFLICT));
            }
        }

        return ApiResponseHelper::sendSuccessResponse();
    }

    /**
     * @throws Throwable
     */
    public function moveItems(int $fromWarehouseId, WarehouseItemsMoveItemsRequest $request): JsonResponse
    {
        /**
         * @var array{
         *     items: array<string,array{
         *      item_id:int,
         *      quantity:float,
         *      to_warehouse_id:int
         *     }>,
         * } $requestedData
         */
        $requestedData = $request->validated();
        $dataToMove = array_map(function ($item) use ($fromWarehouseId) {
            return [
                'from_warehouse_id' => $fromWarehouseId,
                'to_warehouse_id' => $item['to_warehouse_id'],
                'item_id' => $item['item_id'],
                'quantity' => $item['quantity'],
            ];
        }, $requestedData['items']);
        DB::transaction(
            callback: function () use ($dataToMove) {
                foreach ($dataToMove as $item) {
                    $currentItemToMove = WarehouseItem::query()
                        ->where(column: 'item_id', operator: '=', value: $item['item_id'])
                        ->where(column: 'warehouse_id', operator: '=', value: $item['to_warehouse_id'])
                        ->first();
                    if ($currentItemToMove?->quantity < $item['quantity']) {
                        throw new InValidWarehouseItemMoveException('Item quantity out of stock');
                    }

                    WarehouseItem::query()->where('warehouse_id', $item['from_warehouse_id'])
                        ->where('item_id', $item['item_id'])
                        ->decrement('quantity', $item['quantity']);

                    WarehouseItem::query()->where('warehouse_id', $item['to_warehouse_id'])
                        ->where('item_id', $item['item_id'])
                        ->increment('quantity', $item['quantity']);

                }
            },
            attempts: 3);

        return ApiResponseHelper::sendSuccessResponse();
    }

    /**
     * @throws Throwable
     */
    public function updateItems(int $warehouseId, WarehouseItemUpdateItemsRequest $request): JsonResponse
    {
        /**
         * @var array{
         *     items: array<string,array{
         *      item_id:int,
         *      quantity:int|null,
         *      price:float|null
         *     }>,
         * } $requestedData
         */
        $requestedData = $request->validated();

        $dataToUpdate = array_map(function ($item) use ($warehouseId) {
            return [
                'warehouse_id' => $warehouseId,
                'item_id' => $item['item_id'],
                'quantity' => $item['quantity'] ?? null,
                'price' => $item['price'] ?? null,
            ];
        }, $requestedData['items']);

        DB::transaction(
            callback: function () use ($dataToUpdate) {
                WarehouseItem::query()->upsert(
                    values: $dataToUpdate,
                    uniqueBy: ['warehouse_id', 'item_id'],
                    update: ['quantity', 'price']);
            },
            attempts: 3);

        return ApiResponseHelper::sendSuccessResponse();
    }

    public function listItems(int $warehouseId, WarehouseItemsListRequest $request): JsonResponse
    {
        /**
         * @var array{
         *   is_low_stock: bool|null,
         *   is_out_of_stock:bool|null
         * } $requestedData
         */
        $requestedData = $request->validated();
        $results = WarehouseItem::query()
            ->where(column: 'warehouse_id', operator: '=', value: $warehouseId)
            ->with(['item', 'warehouse'])
            ->when(isset($requestedData['is_low_stock']) && $requestedData['is_low_stock'] == true, function (Builder $query) {
                return $query->where(
                    column: 'quantity', operator: '<', value: 5);
            })
            ->when(isset($requestedData['is_out_of_stock']) && $requestedData['is_out_of_stock'] == true, function (Builder $query) {
                return $query->where(
                    column: 'quantity', operator: '=', value: 0);
            })
            ->get();

        return ApiResponseHelper::sendSuccessResponse(new Result(WarehouseItemsListResource::collection($results)));
    }
}

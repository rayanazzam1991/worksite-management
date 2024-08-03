<?php

namespace App\Http\Controllers;

use App\Exceptions\InValidWarehouseItemMoveException;
use App\Helpers\ApiResponse\ApiResponseHelper;
use App\Helpers\ApiResponse\Result;
use App\Http\Requests\WorkSiteItemAddRequest;
use App\Http\Resources\WorkSiteItemListResource;
use App\Models\Item;
use App\Models\WarehouseItem;
use App\Models\WorkSite;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class WorkSiteItemController extends Controller
{
    public function list(int $workSiteId): JsonResponse
    {
        $workSite = WorkSite::query()->with(['items'])->findOrFail($workSiteId);

        return ApiResponseHelper::sendSuccessResponse(new Result(WorkSiteItemListResource::collection($workSite->items)));
    }

    /**
     * @throws \Throwable
     */
    public function addItems(int $workSiteId, WorkSiteItemAddRequest $request): JsonResponse
    {
        $workSite = WorkSite::query()->findOrFail($workSiteId);

        /**
         * @var array{
         *   warehouse_id:int,
         *   items:array<string,array{
         *     item_id:int,
         *     quantity:float,
         *     price:float
         *   }>
         * } $requestedData
         */
        $requestedData = $request->validated();
        DB::transaction(
            callback: function () use ($workSite, $requestedData) {
                foreach ($requestedData['items'] as $item) {
                    $currentItemQtyInWarehouse = WarehouseItem::query()
                        ->where('warehouse_id', $requestedData['warehouse_id'])
                        ->where('item_id', $item['item_id'])
                        ->first()
                        ?->quantity;
                    if ($currentItemQtyInWarehouse < $item['quantity']) {
                        throw new InValidWarehouseItemMoveException('Not enough items in warehouse', Response::HTTP_BAD_REQUEST);
                    }
                    $itemsData = [
                        $item['item_id'] => [
                            'quantity' => $item['quantity'],
                            'price' => $item['price'],
                        ],
                    ];
                    $workSite->items()->syncWithoutDetaching($itemsData);

                    WarehouseItem::query()->where('warehouse_id', $requestedData['warehouse_id'])
                        ->where('item_id', $item['item_id'])
                        ->decrement('quantity', $item['quantity']);
                }
            },
            attempts: 3);

        return ApiResponseHelper::sendSuccessResponse();
    }
}

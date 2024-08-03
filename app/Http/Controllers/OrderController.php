<?php

namespace App\Http\Controllers;

use App\Enums\OrderPriorityEnum;
use App\Enums\OrderStatusEnum;
use App\Exceptions\OrderEditException;
use App\Helpers\ApiResponse\ApiResponseHelper;
use App\Helpers\ApiResponse\Result;
use App\Http\Requests\OrderCreateRequest;
use App\Http\Requests\OrderUpdateRequest;
use App\Http\Resources\OrderDetailsResource;
use App\Http\Resources\OrderListResource;
use App\Models\Order;
use App\Models\OrderItem;
use Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class OrderController extends Controller
{
    /**
     * @throws Throwable
     */
    public function store(OrderCreateRequest $request): JsonResponse
    {
        /**
         * @var array{
         *     work_site_id: int,
         *     items:array<string,array{
         *     item_id:int,
         *     quantity:int
         *     }>,
         *     priority:int|null,
         *     status:int|null,
         * } $requestedData
         */
        $requestedData = $request->validated();
        DB::transaction(callback: function () use ($requestedData, &$order) {
            $order = Order::query()->create([
                'work_site_id' => $requestedData['work_site_id'],
                'priority' => $requestedData['priority'] ?? OrderPriorityEnum::NORMAL->value,
                'status' => $requestedData['status'] ?? OrderStatusEnum::PENDING->value,
                'created_by' => Auth::id(),
            ]);
            $orderItemsData = array_map(function ($item) use ($order) {
                return [
                    'order_id' => $order->id,
                    'item_id' => $item['item_id'],
                    'quantity' => $item['quantity'],

                ];
            }, $requestedData['items']);
            OrderItem::query()->insert($orderItemsData);
        }, attempts: 3);

        return ApiResponseHelper::sendSuccessResponse(new Result(OrderDetailsResource::make($order)));

    }

    /**
     * @throws Throwable
     */
    public function update(OrderUpdateRequest $request, int $orderId): JsonResponse
    {
        $order = Order::query()->findOrFail($orderId);
        $authUser = Auth::user();
        if ($authUser && ! $authUser->hasRole('admin') &&
            $order->status && ! OrderStatusEnum::isAllowedToEditByNonAdmin($order->status)) {
            throw new OrderEditException('You cannot update an order not in pending approval');
        }

        DB::transaction(callback: function () use ($request, $order, $authUser) {

            /**
             * @var array{
             *     work_site_id: int|null,
             *     total_amount:float|null,
             *     status:int|null,
             *     items:array<string,array{
             *     item_id:int|null,
             *     quantity:int|null
             *     }>|null,
             *     priority:int|null
             * } $requestedData
             */
            $requestedData = $request->validated();

            if ($authUser && $authUser->hasRole('site_manager')
                && array_key_exists('status', $requestedData)
                && $requestedData['status']
                && ! OrderStatusEnum::isAllowedToEditBySiteManager($requestedData['status'])) {
                throw new OrderEditException('You are not allowed to update order status', Response::HTTP_FORBIDDEN);
            }
            if ($authUser && $authUser->hasRole('store_keeper')
                && array_key_exists('status', $requestedData)
                && $requestedData['status']
                && ! OrderStatusEnum::isAllowedToEditByStoreKeeper($requestedData['status'])) {
                throw new OrderEditException('You are not allowed to update order status', Response::HTTP_FORBIDDEN);
            }

            $dataToUpdate = array_filter([
                'priority' => $requestedData['priority'] ?? null,
                'total_amount' => $requestedData['total_amount'] ?? null,
                'status' => $requestedData['status'] ?? null,
            ], fn ($item) => $item != null);

            $order->update($dataToUpdate);
            if (isset($requestedData['items']) &&
                count($requestedData['items']) > 0) {
                $orderItemsToUpdateData = array_map(function ($item) use ($order) {
                    return [
                        'order_id' => $order->id,
                        'item_id' => $item['item_id'],
                        'quantity' => $item['quantity'],

                    ];
                }, $requestedData['items']);
                OrderItem::query()->upsert(
                    values: $orderItemsToUpdateData,
                    uniqueBy: ['order_id', 'item_id'],
                    update: ['quantity']
                );
            }
        }, attempts: 3);

        $updatedOrder = $order->refresh();

        return ApiResponseHelper::sendSuccessResponse(new Result(OrderDetailsResource::make($updatedOrder)));
    }

    public function list(): JsonResponse
    {
        $user = Auth::user();
        $orders = Order::query()
            ->when(value: $user && ! $user->hasRole('admin'), callback: function (Builder $query) {
                return $query->where(column: 'created_by',
                    operator: '=', value: Auth::id());
            })
            ->with(['orderCreatedBy'])->get();

        return ApiResponseHelper::sendSuccessResponse(new Result(OrderListResource::collection($orders)));
    }

    public function show(int $orderId): JsonResponse
    {
        $user = Auth::user();
        $order = Order::query()
            ->when(value: $user && ! $user->hasRole('admin'), callback: function (Builder $query) {
                return $query->where(column: 'created_by',
                    operator: '=', value: Auth::id());
            })
            ->with(['orderCreatedBy', 'orderItems.itemDetails'])
            ->findOrFail($orderId);

        return ApiResponseHelper::sendSuccessResponse(new Result(OrderDetailsResource::make($order)));
    }
}

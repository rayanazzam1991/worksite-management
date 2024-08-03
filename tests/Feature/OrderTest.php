<?php

use App\Enums\OrderPriorityEnum;
use App\Enums\OrderStatusEnum;
use App\Models\DailyAttendance;
use App\Models\Item;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Models\WorkSite;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\Response;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;

describe('Order Create', function () {
    beforeEach(function () {
        $this->admin = User::factory()->admin()->create();
        $this->siteManager = User::factory()->siteManager()->create();
        $this->worker = User::factory()->worker()->create();
        $this->workSite1 = WorkSite::factory()->create();
        $this->workSite2 = WorkSite::factory()->create();
        $this->item1 = Item::factory()->create();
        $this->item2 = Item::factory()->create();

    });
    test('As an admin , I can create an order for a worksite or general one', function () {
        $response = actingAs($this->admin)->postJson('api/v1/order/create', [
            'work_site_id' => $this->workSite1->id,
            'items' => [
                [
                    'item_id' => $this->item1->id,
                    'quantity' => 10,
                ],
                [
                    'item_id' => $this->item2->id,
                    'quantity' => 15,
                ],
            ],
            'priority' => OrderPriorityEnum::NORMAL->value,
            'date' => Carbon::today()->toDateString(),
        ])->assertStatus(Response::HTTP_OK);
        $orderId = json_decode($response->content())->data->id;
        assertDatabaseHas('orders', [
            'id' => $orderId,
            'created_by' => $this->admin->id,
            'work_site_id' => $this->workSite1->id,
            'priority' => OrderPriorityEnum::NORMAL->value,
        ]);
        assertDatabaseHas(OrderItem::class, [
            'order_id' => $orderId,
            'item_id' => $this->item1->id,
            'quantity' => 10,
        ]);
        assertDatabaseHas(OrderItem::class, [
            'order_id' => $orderId,
            'item_id' => $this->item2->id,
            'quantity' => 15,
        ]);
    });
    test('As a worksite manager, I cant create an order for a worksite,if i am not in this worksite at this time', function () {
        DailyAttendance::factory()->create([
            'work_site_id' => $this->workSite1->id,
            'employee_id' => $this->siteManager->id,
            'date' => Carbon::now()->addDays(-1),
        ]);
        actingAs($this->siteManager)->postJson('api/v1/order/create', [
            'work_site_id' => $this->workSite1->id,
            'items' => [
                [
                    'item_id' => $this->item1->id,
                    'quantity' => 10,
                ],
                [
                    'item_id' => $this->item2->id,
                    'quantity' => 15,
                ],
            ],
            'priority' => OrderPriorityEnum::NORMAL->value,
            'date' => Carbon::now()->toDateString(),
        ])->assertStatus(Response::HTTP_FORBIDDEN);
    });
    test('As a worksite manager, I can create an order for a worksite, if i am in this worksite at this time', function () {
        DailyAttendance::factory()->create([
            'work_site_id' => $this->workSite1->id,
            'employee_id' => $this->siteManager->id,
            'date' => Carbon::now()->toDateString(),
        ]);
        actingAs($this->siteManager)->postJson('api/v1/order/create', [
            'work_site_id' => $this->workSite1->id,
            'items' => [
                [
                    'item_id' => $this->item1->id,
                    'quantity' => 10,
                ],
                [
                    'item_id' => $this->item2->id,
                    'quantity' => 15,
                ],
            ],
            'priority' => OrderPriorityEnum::NORMAL->value,
            'date' => Carbon::now()->toDateString(),
        ])->assertStatus(Response::HTTP_OK);
    });
    test('As a not worksite manager or admin, I cant create an order for a worksite or general one', function () {
        actingAs($this->worker)->postJson('api/v1/order/create')
            ->assertStatus(Response::HTTP_FORBIDDEN);
    });
});
describe('Order Update', function () {
    beforeEach(function () {

        $this->admin = User::factory()->admin()->create();
        $this->siteManager = User::factory()->siteManager()->create();
        $this->worker = User::factory()->worker()->create();
        $this->workSite = WorkSite::factory()->create();
        $this->item1 = Item::factory()->create();
        $this->item2 = Item::factory()->create();
    });
    test('As a worksite manager, I can update an order items, while its pending', function () {
        $order = Order::factory()->create([
            'work_site_id' => $this->workSite->id,
            'status' => OrderStatusEnum::PENDING->value,
            'created_by' => $this->siteManager->id,
        ]);
        DailyAttendance::factory()->create([
            'work_site_id' => $this->workSite->id,
            'employee_id' => $this->siteManager->id,
            'date' => Carbon::now()->toDateString(),
        ]);
        $orderItem = OrderItem::factory()->create([
            'order_id' => $order->id,
            'item_id' => $this->item1->id,
            'quantity' => 10,
        ]);
        $response = actingAs($this->siteManager)->putJson('api/v1/order/update/'.$order->id, [
            'items' => [
                [

                    'item_id' => $orderItem->item_id,
                    'quantity' => 20,
                ],
            ],
        ]);
        $response->assertStatus(Response::HTTP_OK);
        assertDatabaseHas(OrderItem::class, [
            'order_id' => $order->id,
            'item_id' => $orderItem->item_id,
            'quantity' => 20,
        ]);
    });
    test('As a worksite manager, I cant update an order items, while its processed', function () {
        $order = Order::factory()->create([
            'status' => OrderStatusEnum::APPROVED->value,
        ]);
        $orderItem = OrderItem::factory()->create([
            'order_id' => $order->id,
            'item_id' => $this->item1->id,
            'quantity' => 10,
        ]);
        $response = actingAs($this->siteManager)->putJson('api/v1/order/update/'.$order->id, [
            'items' => [
                [

                    'item_id' => $orderItem->item_id,
                    'quantity' => 20,
                ],
            ],
        ]);
        $response->assertStatus(Response::HTTP_FORBIDDEN)
            ->assertJsonFragment([
                'message' => 'You cannot update an order not in pending approval',
            ]);
        assertDatabaseHas(OrderItem::class, [
            'order_id' => $order->id,
            'item_id' => $orderItem->item_id,
            'quantity' => 10,
        ]);

    });
    test('As an admin I can update an order items at any status', function () {
        $order = Order::factory()->create([
            'status' => OrderStatusEnum::APPROVED->value,
        ]);
        $orderItem = OrderItem::factory()->create([
            'order_id' => $order->id,
            'item_id' => $this->item1->id,
            'quantity' => 10,
        ]);
        $response = actingAs($this->admin)->putJson('api/v1/order/update/'.$order->id, [
            'items' => [
                [

                    'item_id' => $orderItem->item_id,
                    'quantity' => 20,
                ],
            ],
        ]);
        $response->assertStatus(Response::HTTP_OK);
        assertDatabaseHas(OrderItem::class, [
            'order_id' => $order->id,
            'item_id' => $orderItem->item_id,
            'quantity' => 20,
        ]);

    });
});
describe('Order List', function () {
    beforeEach(function () {

        $this->admin = User::factory()->admin()->create();
        $this->siteManager1 = User::factory()->siteManager()->create();
        $this->siteManager2 = User::factory()->siteManager()->create();
        $this->worker = User::factory()->worker()->create();
        $this->workSite1 = WorkSite::factory()->create();
        $this->workSite2 = WorkSite::factory()->create();
        $this->item1 = Item::factory()->create();
        $this->item2 = Item::factory()->create();

        $this->employeeAttendance = DailyAttendance::factory()->create([
            'employee_id' => $this->siteManager1->id,
            'work_site_id' => $this->workSite1->id,
            'date' => Carbon::today()->toDateString(),

        ]);

    });
    test('As a worksite manager, I can see list of my orders', function () {

        $order1 = Order::factory()->create([
            'status' => OrderStatusEnum::PENDING->value,
            'priority' => OrderPriorityEnum::LOW->value,
            'work_site_id' => $this->workSite1->id,
            'created_by' => $this->siteManager1->id,
        ]);
        Order::factory()->create([
            'work_site_id' => $this->workSite2->id,
            'created_by' => $this->siteManager2->id,
        ]);
        $response = actingAs($this->siteManager1)->getJson('api/v1/order/list');
        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonPath('data', [
                [
                    'id' => $order1->id,
                    'workSite' => $this->workSite1->title,
                    'total_amount' => number_format($order1->total_amount, 2, '.', ''),
                    'status' => OrderStatusEnum::from($order1->status)->name,
                    'priority' => OrderPriorityEnum::from($order1->priority)->name,
                    'created_by' => $this->siteManager1->fullName,
                ],
            ]);
    });
    test('As an admin, I can see list of all orders in the system', function () {
        $order1 = Order::factory()->create([
            'status' => OrderStatusEnum::PENDING->value,
            'priority' => OrderPriorityEnum::LOW->value,
            'work_site_id' => $this->workSite1->id,
            'created_by' => $this->siteManager1->id,
        ]);
        $order2 = Order::factory()->create([
            'status' => OrderStatusEnum::PENDING->value,
            'priority' => OrderPriorityEnum::LOW->value,
            'work_site_id' => $this->workSite2->id,
            'created_by' => $this->siteManager2->id,
        ]);
        $response = actingAs($this->admin)->getJson('api/v1/order/list');
        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonPath('data', [
                [
                    'id' => $order1->id,
                    'workSite' => $this->workSite1->title,
                    'total_amount' => number_format($order1->total_amount, 2, '.', ''),
                    'status' => OrderStatusEnum::from($order1->status)->name,
                    'priority' => OrderPriorityEnum::from($order1->priority)->name,
                    'created_by' => $this->siteManager1->fullName,
                ],
                [
                    'id' => $order2->id,
                    'workSite' => $this->workSite2->title,
                    'total_amount' => number_format($order2->total_amount, 2, '.', ''),
                    'status' => OrderStatusEnum::from($order2->status)->name,
                    'priority' => OrderPriorityEnum::from($order2->priority)->name,
                    'created_by' => $this->siteManager2->fullName,
                ],
            ]);
    });
});

describe('Order Detail', function () {
    beforeEach(function () {

        $this->admin = User::factory()->admin()->create();
        $this->siteManager1 = User::factory()->siteManager()->create();
        $this->siteManager2 = User::factory()->siteManager()->create();
        $this->worker = User::factory()->worker()->create();
        $this->workSite1 = WorkSite::factory()->create();
        $this->workSite2 = WorkSite::factory()->create();
        $this->item1 = Item::factory()->create();
        $this->item2 = Item::factory()->create();

        $this->employeeAttendance = DailyAttendance::factory()->create([
            'employee_id' => $this->siteManager1->id,
            'work_site_id' => $this->workSite1->id,
            'date' => Carbon::today()->toDateString(),
        ]);

    });
    test('As a worksite manager, I can see details of my order', function () {
        $order = Order::factory()->create([
            'status' => OrderStatusEnum::PENDING->value,
            'priority' => OrderPriorityEnum::LOW->value,
            'work_site_id' => $this->workSite1->id,
            'created_by' => $this->siteManager1->id,
        ]);
        $orderItem = OrderItem::factory()->create([
            'order_id' => $order->id,
            'item_id' => $this->item1->id,
            'quantity' => 10,
        ]);
        $response = actingAs($this->siteManager1)->getJson('api/v1/order/show/'.$order->id);
        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonFragment(
                [
                    'id' => $order->id,
                    'workSite' => $this->workSite1->title,
                    'order_items' => [
                        [
                            'id' => $orderItem->id,
                            'item' => [
                                'id' => $this->item1->id,
                                'name' => $this->item1->name,
                                'description' => $this->item1->description,
                                'item_category' => [
                                    'id' => $this->item1->category->id,
                                    'name' => $this->item1->category->name,
                                ],
                            ],
                            'quantity' => 10,
                            'price' => number_format($orderItem->price, 2, '.', ''),
                        ],
                    ],
                    'total_amount' => number_format($order->total_amount, 2, '.', ''),
                    'status' => 'PENDING',
                    'priority' => 'LOW',
                    'created_by' => $this->siteManager1->fullName,
                ]
            );
    });
    test('As a worksite manager, I cant see details of order of others', function () {
        $order = Order::factory()->create([
            'status' => OrderStatusEnum::PENDING->value,
            'priority' => OrderPriorityEnum::LOW->value,
            'work_site_id' => $this->workSite1->id,
            'created_by' => $this->siteManager2->id,
        ]);
        $response = actingAs($this->siteManager1)->getJson('api/v1/order/show/'.$order->id);
        $response->assertStatus(Response::HTTP_NOT_FOUND)
            ->assertJsonPath('data', null);
    });
    test('As an admin, I can see details of any order in the system', function () {
        $order = Order::factory()->create([
            'status' => OrderStatusEnum::PENDING->value,
            'priority' => OrderPriorityEnum::LOW->value,
            'work_site_id' => $this->workSite1->id,
            'created_by' => $this->siteManager1->id,
        ]);
        $orderItem = OrderItem::factory()->create([
            'order_id' => $order->id,
            'item_id' => $this->item1->id,
            'quantity' => 10,
        ]);
        $response = actingAs($this->admin)->getJson('api/v1/order/show/'.$order->id);
        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonPath('data', [
                'id' => $order->id,
                'workSite' => $this->workSite1->title,
                'order_items' => [
                    [
                        'id' => $orderItem->id,
                        'item' => [
                            'id' => $this->item1->id,
                            'name' => $this->item1->name,
                            'description' => $this->item1->description,
                            'item_category' => [
                                'id' => $this->item1->category->id,
                                'name' => $this->item1->category->name,
                            ],
                        ],
                        'quantity' => 10,
                        'price' => number_format($orderItem->price, 2, '.', ''),
                    ],
                ],
                'total_amount' => number_format($order->total_amount, 2, '.', ''),
                'status' => 'PENDING',
                'priority' => 'LOW',
                'created_by' => $this->siteManager1->fullName,
            ]);
    });
});

describe('Order Status', function () {
    beforeEach(function () {

        $this->admin = User::factory()->admin()->create();
        $this->siteManager1 = User::factory()->siteManager()->create();
        $this->siteManager2 = User::factory()->siteManager()->create();
        $this->worker = User::factory()->worker()->create();
        $this->workSite1 = WorkSite::factory()->create();
        $this->workSite2 = WorkSite::factory()->create();
        $this->item1 = Item::factory()->create();
        $this->item2 = Item::factory()->create();

        $this->storeKeeper = User::factory()->storeKeeper()->create();

        $this->employeeAttendance = DailyAttendance::factory()->create([
            'employee_id' => $this->siteManager1->id,
            'work_site_id' => $this->workSite1->id,
            'date' => Carbon::today()->toDateString(),
        ]);
        $this->order = Order::factory()->create([
            'status' => OrderStatusEnum::PENDING->value,
        ]);

    });
    test('As a worksite manager, I can update the status of the order to received to worksite', function () {
        actingAs($this->siteManager1)->putJson('api/v1/order/update/'.$this->order->id, [
            'status' => OrderStatusEnum::RECEIVED_TO_WORKSITE->value,
        ])->assertStatus(Response::HTTP_OK);
    });
    test('As a worksite manager, I cant update the status of the order to other than received to worksite only', function () {
        actingAs($this->siteManager1)->putJson('api/v1/order/update/'.$this->order->id, [
            'status' => OrderStatusEnum::DELIVERED_TO_WAREHOUSE->value,
        ])->assertStatus(Response::HTTP_FORBIDDEN);
    });
    test('As a store keeper, I can update the status of the order to Delivered to warehouse only', function () {
        actingAs($this->storeKeeper)->putJson('api/v1/order/update/'.$this->order->id, [
            'status' => OrderStatusEnum::DELIVERED_TO_WAREHOUSE->value,
        ])->assertStatus(Response::HTTP_OK);
    });
    test('As a store keeper, I cant update the status of the order to other than Delivered to warehouse', function () {
        actingAs($this->storeKeeper)->putJson('api/v1/order/update/'.$this->order->id, [
            'status' => OrderStatusEnum::RECEIVED_TO_WORKSITE->value,
        ])->assertStatus(Response::HTTP_FORBIDDEN);
    });
    test('As an admin, I can update the status of the order to any status', function () {
        actingAs($this->admin)->putJson('api/v1/order/update/'.$this->order->id, [
            'status' => OrderStatusEnum::RECEIVED_TO_WORKSITE->value,
        ])->assertStatus(Response::HTTP_OK);
        actingAs($this->admin)->putJson('api/v1/order/update/'.$this->order->id, [
            'status' => OrderStatusEnum::PENDING->value,
        ])->assertStatus(Response::HTTP_OK);
        actingAs($this->admin)->putJson('api/v1/order/update/'.$this->order->id, [
            'status' => OrderStatusEnum::REJECTED->value,
        ])->assertStatus(Response::HTTP_OK);
        actingAs($this->admin)->putJson('api/v1/order/update/'.$this->order->id, [
            'status' => OrderStatusEnum::CANCELLED->value,
        ])->assertStatus(Response::HTTP_OK);
        actingAs($this->admin)->putJson('api/v1/order/update/'.$this->order->id, [
            'status' => OrderStatusEnum::ORDERED_FROM_SUPPLIER->value,
        ])->assertStatus(Response::HTTP_OK);
        actingAs($this->admin)->putJson('api/v1/order/update/'.$this->order->id, [
            'status' => OrderStatusEnum::CANCELLED_FROM_SUPPLIER->value,
        ])->assertStatus(Response::HTTP_OK);
        actingAs($this->admin)->putJson('api/v1/order/update/'.$this->order->id, [
            'status' => OrderStatusEnum::DELIVERED_FROM_SUPPLIER->value,
        ])->assertStatus(Response::HTTP_OK);
        actingAs($this->admin)->putJson('api/v1/order/update/'.$this->order->id, [
            'status' => OrderStatusEnum::RECEIVED_TO_WORKSITE->value,
        ])->assertStatus(Response::HTTP_OK);
        actingAs($this->admin)->putJson('api/v1/order/update/'.$this->order->id, [
            'status' => OrderStatusEnum::SENT_TO_WAREHOUSE->value,
        ])->assertStatus(Response::HTTP_OK);
        actingAs($this->admin)->putJson('api/v1/order/update/'.$this->order->id, [
            'status' => OrderStatusEnum::DELIVERED_TO_WAREHOUSE->value,
        ])->assertStatus(Response::HTTP_OK);
    });
});

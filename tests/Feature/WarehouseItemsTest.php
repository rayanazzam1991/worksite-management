<?php

use App\Models\Address;
use App\Models\Item;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseItem;
use App\Models\WorkSite;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\Response;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;

describe('Warehouse Items', function () {
    beforeEach(function () {

        $this->workSite = WorkSite::factory()->create();
        $this->admin = User::factory()->admin()->create();
        $this->notAdmin = User::factory()->worker()->create();
        $this->address = Address::factory()->create();
        $this->warehouse = Warehouse::factory()->create();

        $this->otherWarehouse = Warehouse::factory()->create();

        $this->item1 = Item::factory()->create();
        $this->item2 = Item::factory()->create();
        $this->item3 = Item::factory()->create();
        $this->item4 = Item::factory()->create();

        $this->supplier = Supplier::factory()->create();

        WarehouseItem::factory()->create([
            'warehouse_id' => $this->warehouse->id,
            'item_id' => $this->item1->id,
            'quantity' => 5,
        ]);

        WarehouseItem::factory()->create([
            'warehouse_id' => $this->otherWarehouse->id,
            'item_id' => $this->item1->id,
            'quantity' => 10,
        ]);

        $this->warehouseItem = WarehouseItem::factory()->create([
            'warehouse_id' => $this->warehouse->id,
            'item_id' => $this->item3->id,
            'quantity' => 3,
        ]);

        $this->warehouseItem = WarehouseItem::factory()->create([
            'warehouse_id' => $this->warehouse->id,
            'item_id' => $this->item4->id,
            'quantity' => 0,
        ]);

    });
    it('should return error if adding same item twice to a warehouse', function () {
        actingAs($this->admin)->postJson('/api/v1/warehouse/'.$this->warehouse->id.'/items/add', [
            'items' => [
                [
                    'item_id' => $this->item1->id,
                    'quantity' => 1,
                    'price' => 20,
                ],
                [
                    'item_id' => $this->item1->id,
                    'quantity' => 10,
                    'price' => 30,
                ],
            ],
            'supplier_id' => $this->supplier->id,
            'date' => Carbon::now()->format('Y-m-d H:i:s'),
        ])
            ->assertStatus(Response::HTTP_CONFLICT)
            ->assertJsonFragment([
                'message' => 'Item already exists in this warehouse',
            ]);
    });
    test('adding items to warehouse from a supplier', function () {

        $item1 = Item::factory()->create();
        $item2 = Item::factory()->create();

        actingAs($this->admin)->postJson('/api/v1/warehouse/'.$this->warehouse->id.'/items/add', [
            'items' => [
                [
                    'item_id' => $item1->id,
                    'quantity' => 1,
                    'price' => 20,
                ],
                [
                    'item_id' => $item2->id,
                    'quantity' => 10,
                    'price' => 30,
                ],
            ],
            'supplier_id' => $this->supplier->id,
            'date' => Carbon::now()->format('Y-m-d H:i:s'),
        ])
            ->assertStatus(Response::HTTP_OK);
    });
    it('should prevent adding negative quantity for an item', function () {
        actingAs($this->admin)->postJson('/api/v1/warehouse/'.$this->warehouse->id.'/items/add', [
            'items' => [
                [
                    'item_id' => $this->item1->id,
                    'quantity' => -1,
                    'price' => 20,
                ],
            ],
            'supplier_id' => $this->supplier->id,
            'date' => Carbon::now()->format('Y-m-d H:i:s'),
        ])->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    });
    test('moving item from one warehouse to other', function () {
        actingAs($this->admin)->postJson('/api/v1/warehouse/'.$this->warehouse->id.'/items/move', [
            'items' => [
                [
                    'item_id' => $this->item1->id,
                    'quantity' => 2,
                    'to_warehouse_id' => $this->otherWarehouse->id,
                ],
            ],

        ])
            ->assertStatus(Response::HTTP_OK);
        assertDatabaseHas(WarehouseItem::class, [
            'item_id' => $this->item1->id,
            'warehouse_id' => $this->otherWarehouse->id,
            'quantity' => 12,
        ]);
        assertDatabaseHas(WarehouseItem::class, [
            'item_id' => $this->item1->id,
            'warehouse_id' => $this->warehouse->id,
            'quantity' => 3,
        ]);
    });
    it('should prevent moving item if no sufficient items in origin warehouse', function () {
        actingAs($this->admin)->postJson('/api/v1/warehouse/'.$this->warehouse->id.'/items/move', [
            'items' => [
                [
                    'item_id' => $this->item1->id,
                    'quantity' => 15,
                    'to_warehouse_id' => $this->otherWarehouse->id,
                ],
            ],

        ])->assertStatus(Response::HTTP_BAD_REQUEST);
        assertDatabaseHas(WarehouseItem::class, [
            'item_id' => $this->item1->id,
            'warehouse_id' => $this->otherWarehouse->id,
            'quantity' => 10,
        ]);
        assertDatabaseHas(WarehouseItem::class, [
            'item_id' => $this->item1->id,
            'warehouse_id' => $this->warehouse->id,
            'quantity' => 5,
        ]);
    });
    test('updating quantity and price for multiple items', function () {
        actingAs($this->admin)->postJson('/api/v1/warehouse/'.$this->warehouse->id.'/items/update', [
            'items' => [
                [
                    'item_id' => $this->item1->id,
                    'quantity' => 10,
                    'price' => 245,
                ],
                [
                    'item_id' => $this->item2->id,
                    'quantity' => 30,
                    'price' => 249,
                ],
            ],
        ]);
        assertDatabaseHas(WarehouseItem::class, [
            'item_id' => $this->item1->id,
            'warehouse_id' => $this->warehouse->id,
            'quantity' => 10,
            'price' => 245,
        ]);
        assertDatabaseHas(WarehouseItem::class, [
            'item_id' => $this->item2->id,
            'warehouse_id' => $this->warehouse->id,
            'quantity' => 30,
            'price' => 249,
        ]);
    });
    test('getting list of low stock items', function () {
        $item = Item::factory()->create();
        $warehouse = Warehouse::factory()->create();
        $warehouseItem = WarehouseItem::factory()->create([
            'warehouse_id' => $warehouse->id,
            'item_id' => $item->id,
            'quantity' => 3,
        ]);

        actingAs($this->admin)->postJson('/api/v1/warehouse/'.$warehouse->id.'/items/list', [
            'is_low_stock' => true,
        ])
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonPath('data', [
                [
                    'item' => [
                        'id' => $item->id,
                        'name' => $item->name,
                        'description' => $item->description,
                        'item_category' => [
                            'id' => $item->category->id,
                            'name' => $item->category->name,
                        ],
                    ],
                    'warehouse' => [
                        'name' => $warehouse->name,
                        'address' => [
                            'id' => $warehouse->address->id,
                            'title' => $warehouse->address->title,
                            'city' => $warehouse->address->city->name,
                            'street' => $warehouse->address->street,
                            'state' => $warehouse->address->state,
                            'zipCode' => $warehouse->address->zipcode,
                        ],
                    ],
                    'quantity' => $warehouseItem->quantity,
                    'price' => number_format($warehouseItem->price, 2),
                ],
            ]);
    });
    test('getting list of out of stock items', function () {
        actingAs($this->admin)->postJson('/api/v1/warehouse/'.$this->warehouse->id.'/items/list', [
            'is_out_of_stock' => true,
        ])
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonPath('data', [
                [
                    'item' => [
                        'id' => $this->item4->id,
                        'name' => $this->item4->name,
                        'description' => $this->item4->description,
                        'item_category' => [
                            'id' => $this->item4->category->id,
                            'name' => $this->item4->category->name,
                        ],
                    ],
                    'warehouse' => [
                        'name' => $this->warehouse->name,
                        'address' => [
                            'id' => $this->warehouse->address->id,
                            'title' => $this->warehouse->address->title,
                            'city' => $this->warehouse->address->city->name,
                            'street' => $this->warehouse->address->street,
                            'state' => $this->warehouse->address->state,
                            'zipCode' => $this->warehouse->address->zipcode,
                        ],
                    ],
                    'quantity' => $this->warehouseItem->quantity,
                    'price' => number_format($this->warehouseItem->price, 2),
                ],
            ]);
    });
});

//    it('should have the option to attach a wareHouse with a workSite or make it as main workHouse
//    without a workSite', function () {
//    });
//    it('should assign store keeper to a wareHouse, and at least one for each', function () {
//    });
//    it('should be able to move items between warehouses and make data consist between them', function () {
//    });
//    it('should be able to add new items to a warehouse from any external supplier', function () {
//    });
//    it('should track all movements between warehouses', function () {
//    });
//    it('should move items to a workSite from its own warehouse only', function () {
//    });
//    it('should track all items movements between worksite and its warehouse', function () {
//    });
//
//    test('if items entered to a worksite are the same the drop off its wareHouse', function () {
//    });
//});

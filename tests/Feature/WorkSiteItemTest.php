<?php

use App\Models\Item;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseItem;
use App\Models\WorkSite;
use App\Models\WorkSiteItem;
use Symfony\Component\HttpFoundation\Response;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;

describe('WorkSite Item assign', function () {

    beforeEach(function () {

        $this->notAdmin = User::factory()->worker()->create();
        $this->admin = User::factory()->admin()->create();

        $this->workSite = Worksite::factory()->create();
        $this->item1 = Item::factory()->create();
        $this->item2 = Item::factory()->create();
        $this->item3 = Item::factory()->create();
        $this->warehouse = Warehouse::factory()->create();
        WarehouseItem::factory()->create([
            'warehouse_id' => $this->warehouse->id,
            'item_id' => $this->item1->id,
            'quantity' => 100,
            'price' => 100,

        ]);
        WarehouseItem::factory()->create([
            'warehouse_id' => $this->warehouse->id,
            'item_id' => $this->item2->id,
            'quantity' => 200,
            'price' => 300,

        ]);
        WorkSiteItem::factory()->create([
            'work_site_id' => $this->workSite->id,
            'item_id' => $this->item2->id,
            'quantity' => 100,
            'price' => 300,
        ]);
        WarehouseItem::factory()->create([
            'warehouse_id' => $this->warehouse->id,
            'item_id' => $this->item3->id,
            'quantity' => 5,
            'price' => 300,
        ]);

    });

    it('should prevent non auth adding a item to a workSite', function () {
        $response = postJson('/api/v1/workSite/'.$this->workSite->id.'/item/add');
        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    });
    it('should prevent non admin adding a item to a workSite', function () {
        $response = actingAs($this->notAdmin)
            ->postJson('/api/v1/workSite/'.$this->workSite->id.'/item/add');
        $response->assertStatus(Response::HTTP_FORBIDDEN);
    });
    it('should return not found error when workSite not found', function () {
        $undefinedWorkSiteId = rand(222, 333);
        $response = actingAs($this->admin)
            ->postJson('/api/v1/workSite/'.$undefinedWorkSiteId.'/item/add', [
                'warehouse_id' => $this->warehouse->id,
                'items' => [
                    [
                        'item_id' => $this->item1->id,
                        'quantity' => 10,
                        'price' => 300,
                    ],
                    [
                        'item_id' => $this->item2->id,
                        'quantity' => 20,
                        'price' => 300,
                    ],
                ],
            ]);
        $response->assertStatus(Response::HTTP_NOT_FOUND);
    });
    it('should add valid item to a valid workSite', function () {
        $response = actingAs($this->admin)
            ->postJson('/api/v1/workSite/'.$this->workSite->id.'/item/add', [
                'warehouse_id' => $this->warehouse->id,
                'items' => [
                    [
                        'item_id' => $this->item1->id,
                        'quantity' => 10,
                        'price' => 300,
                    ],
                    [
                        'item_id' => $this->item2->id,
                        'quantity' => 20,
                        'price' => 300,
                    ],
                ],
            ]);
        $response->assertOk();
        assertDatabaseHas(WorkSiteItem::class, [
            'work_site_id' => $this->workSite->id,
            'item_id' => $this->item1->id,
            'quantity' => 10,
            'price' => '300.00',
        ]);
        assertDatabaseHas(WorkSiteItem::class, [
            'work_site_id' => $this->workSite->id,
            'item_id' => $this->item2->id,
            'quantity' => 20,
            'price' => '300.00',
        ]);
        assertDatabaseHas(WarehouseItem::class, [
            'warehouse_id' => $this->warehouse->id,
            'item_id' => $this->item1->id,
            'quantity' => 90,
        ]);
        assertDatabaseHas(WarehouseItem::class, [
            'warehouse_id' => $this->warehouse->id,
            'item_id' => $this->item2->id,
            'quantity' => 180,
        ]);
    });
    it('should prevent move quantity of item that not available in warehouse', function () {
        $response = actingAs($this->admin)
            ->postJson('/api/v1/workSite/'.$this->workSite->id.'/item/add', [
                'warehouse_id' => $this->warehouse->id,
                'items' => [
                    [
                        'item_id' => $this->item3->id,
                        'quantity' => 6,
                        'price' => 300,
                    ],
                ],
            ]);
        $response->assertStatus(Response::HTTP_BAD_REQUEST);
        assertDatabaseHas(WarehouseItem::class, [
            'warehouse_id' => $this->warehouse->id,
            'item_id' => $this->item3->id,
            'quantity' => 5,
        ]);
    });
});
describe('WorkSite Item list', function () {

    beforeEach(function () {

        $this->notAdmin = User::factory()->worker()->create();
        $this->admin = User::factory()->admin()->create();

        $this->workSite = Worksite::factory()->create();
        $this->item = Item::factory()->create([
            'name' => 'Iron',
        ]);
        $this->workSiteItem = WorkSiteItem::factory()->create([
            'quantity' => 10,
            'price' => 3000,
            'work_site_id' => $this->workSite->id,
            'item_id' => $this->item->id,
        ]);
    });

    it('should prevent non auth show list items of a workSite', function () {
        $response = getJson('/api/v1/workSite/'.$this->workSite->id.'/item/list');
        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    });
    it('should prevent non admin show list items of a workSite', function () {
        $response = actingAs($this->notAdmin)
            ->getJson('/api/v1/workSite/'.$this->workSite->id.'/item/list');
        $response->assertStatus(Response::HTTP_FORBIDDEN);
    });
    it('should return not found error when workSite not found', function () {
        $undefinedWorkSiteId = rand(222, 333);
        $response = actingAs($this->admin)
            ->getJson('/api/v1/workSite/'.$undefinedWorkSiteId.'/item/list');
        $response->assertStatus(Response::HTTP_NOT_FOUND);
    });
    it('should return list of items of a  valid workSite', function () {
        $response = actingAs($this->admin)
            ->getJson('/api/v1/workSite/'.$this->workSite->id.'/item/list');
        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    [
                        'id',
                        'item',
                        'quantity',
                        'price',
                    ],
                ],
            ])
            ->assertJsonFragment([
                'id' => $this->item->id,
                'item' => $this->item->name,
                'quantity' => 10,
                'price' => '3000.00',
            ]);
    });
});

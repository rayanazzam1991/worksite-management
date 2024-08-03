<?php

use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\User;
use Symfony\Component\HttpFoundation\Response;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertSoftDeleted;
use function Pest\Laravel\deleteJson;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;
use function Pest\Laravel\putJson;

describe('Item Category routes check', function () {
    it('should have all routes for /item/category/', function () {
        $this->artisan('optimize:clear');
        // Define the expected route names
        $expectedRouteNames = [
            'item.category.create',
            'item.category.update',
            'item.category.list',
            'item.category.show',
            'item.category.delete',
        ];

        // Collect routes and filter based on the prefix
        $customerRoutes = collect(Route::getRoutes())->filter(function ($route) {
            return str_starts_with($route->uri, 'api/v1/item/{itemId}/category');
        });

        // Assert that only the expected routes exist
        $customerRoutes->each(function ($route) use ($expectedRouteNames) {
            $this->assertTrue(in_array($route->getName(), $expectedRouteNames),
                "Route {$route->getName()} does not match expected routes.");

        });
        // Assert that there are routes found for /item/category/
        $this->assertFalse($customerRoutes->isEmpty(), 'No routes found for /item/category');

    });

});
describe('Item Category Create', function () {

    beforeEach(function () {

        $this->notAdmin = User::factory()->worker()->create(['email' => 'not_admin@admin.com']);
        $this->admin = User::factory()->admin()->create(['email' => 'admin@admin.com']);
        $this->item = Item::factory()->create();

    });
    it('should prevent non auth creating new Item Category', function () {
        $response = postJson('/api/v1/item/'.$this->item->id.'/category/create');
        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    });
    it('should prevent non admin creating new Item', function () {

        $response = actingAs($this->notAdmin)->postJson('/api/v1/item/'.$this->item->id.'/category/create');
        $response->assertStatus(Response::HTTP_FORBIDDEN);
    });
    it('should return validation error when data is missed', function () {
        $response = actingAs($this->admin)->postJson('/api/v1/item/'.$this->item->id.'/category/create', []);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    });
    it('should create new Item with valid data', function () {
        $response = actingAs($this->admin)->postJson('/api/v1/item/'.$this->item->id.'/category/create', [
            'name' => 'new',
            'description' => 'new',
        ]);
        $response->assertOk();
    });
});
describe('Item Category Update', function () {

    beforeEach(function () {

        $this->notAdmin = User::factory()->worker()->create(['email' => 'not_admin@admin.com']);
        $this->admin = User::factory()->admin()->create(['email' => 'admin@admin.com']);

        $this->item = Item::factory()->create();
        $this->itemCategory = \App\Models\ItemCategory::factory()->create(['name' => 'new']);

    });

    it('should prevent non auth updating existed ItemCategory', function () {
        $response = putJson('/api/v1/item/'.$this->item->id.'/category/update/'.$this->itemCategory->id, [
            'name' => 'new1',
        ]);
        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    });
    it('should prevent non admin updating existed ItemCategory', function () {

        $response = actingAs($this->notAdmin)
            ->putJson('/api/v1/item/'.$this->item->id.'/category/update/'.$this->itemCategory->id, [
                'name' => 'new1',
            ]);
        $response->assertStatus(Response::HTTP_FORBIDDEN);
    });
    it('should not return validation error when data is missed', function () {

        $response = actingAs($this->admin)
            ->putJson('/api/v1/item/'.$this->item->id.'/category/update/'.$this->itemCategory->id, []);

        $response->assertStatus(Response::HTTP_OK);
    });
    it('should update existed Item with valid data', function () {

        $response = actingAs($this->admin)
            ->putJson('/api/v1/item/'.$this->item->id.'/category/update/'.$this->itemCategory->id, [
                'name' => 'new1',
            ]);
        assertDatabaseHas('item_categories', ['name' => 'new1']);
        $response->assertOk();
    });
});
describe('Item Category List', function () {

    beforeEach(function () {

        $this->notAdmin = User::factory()->worker()->create(['email' => 'not_admin@admin.com']);
        $this->admin = User::factory()->admin()->create(['email' => 'admin@admin.com']);

        $this->item = Item::factory()->create();

        $this->itemCategory1 = ItemCategory::factory()->create(['name' => 'item 1']);
        $this->itemCategory2 = ItemCategory::factory()->create(['name' => 'item 2']);
        $this->itemCategory3 = ItemCategory::factory()->create(['name' => 'item 3']);

    });
    it('should prevent non auth updating existed item', function () {
        $response = getJson('/api/v1/item/'.$this->item->id.'/category/list');
        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    });
    it('should return data', function () {
        $response = actingAs($this->admin)->getJson('/api/v1/item/'.$this->item->id.'/category/list');
        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonCount(4, 'data')
            ->assertJsonFragment(['name' => 'item 1']);
    });
});
describe('Item Category Details', function () {

    beforeEach(function () {

        $this->notAdmin = User::factory()->worker()->create(['email' => 'not_admin@admin.com']);
        $this->admin = User::factory()->admin()->create(['email' => 'admin@admin.com']);

        $this->itemCategory = ItemCategory::factory()->create(['id' => 10, 'name' => 'item 10']);
        $this->item = Item::factory()->create();

    });
    it('should prevent non auth show item', function () {
        $response = getJson('/api/v1/item/'.$this->item->id.'/category/show/'.$this->itemCategory->id);
        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    });
    it('should return not found for un-existed item', function () {
        $nonExisted = rand(222, 333);
        $response = actingAs($this->admin)
            ->getJson('/api/v1/item/'.$this->item->id.'/category/show/'.$nonExisted);
        $response->assertStatus(Response::HTTP_NOT_FOUND);
    });
    it('should return data', function () {
        $response = actingAs($this->admin)
            ->getJson('/api/v1/item/'.$this->item->id.'/category/show/'.$this->itemCategory->id);
        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonPath('data',
                [
                    'id' => $this->itemCategory->id,
                    'name' => $this->itemCategory->name,
                ]
            );
    });
});
describe('Item Category Delete', function () {

    beforeEach(function () {

        $this->notAdmin = User::factory()->worker()->create(['email' => 'not_admin@admin.com']);
        $this->admin = User::factory()->admin()->create(['email' => 'admin@admin.com']);

        $this->item = Item::factory()->create();
        $this->itemCategory = ItemCategory::factory()->create(['id' => 10, 'name' => 'item 10']);

    });
    it('should prevent non auth delete itemCategory', function () {
        $response = deleteJson('/api/v1/item/'.$this->item->id.'/category/delete/'.$this->itemCategory->id);
        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    });
    it('should prevent non admin delete existed itemCategory', function () {
        $response = actingAs($this->notAdmin)
            ->deleteJson('/api/v1/item/'.$this->item->id.'/category/delete/'.$this->itemCategory->id);
        $response->assertStatus(Response::HTTP_FORBIDDEN);
    });
    it('should return not found for un-existed itemCategory', function () {
        $nonExisted = rand(222, 333);
        $response = actingAs($this->admin)
            ->deleteJson('/api/v1/item/'.$this->item->id.'/category/delete/'.$nonExisted);
        $response->assertStatus(Response::HTTP_NOT_FOUND);
    });
    it('should delete itemCategory from database', function () {
        assertDatabaseCount(ItemCategory::class, 2);
        actingAs($this->admin)
            ->deleteJson('/api/v1/item/'.$this->item->id.'/category/delete/'.$this->itemCategory->id);
        assertSoftDeleted('item_categories', ['id' => $this->itemCategory->id]);
        $count = \App\Models\ItemCategory::query()->get()->count();
        // Assert the count
        $this->assertEquals(1, $count, 'The count of non deleted should be 1');

    });
});

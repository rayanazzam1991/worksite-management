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

describe('Item routes check', function () {
    it('should have all routes for /item/', function () {
        $this->artisan('optimize:clear');
        // Define the expected route names
        $expectedRouteNames = [
            'item.create',
            'item.update',
            'item.list',
            'item.show',
            'item.delete',
            'item.category.create',
            'item.category.update',
            'item.category.list',
            'item.category.show',
            'item.category.delete',
        ];

        // Collect routes and filter based on the prefix
        $customerRoutes = collect(Route::getRoutes())->filter(function ($route) {
            return str_starts_with($route->uri, 'api/v1/item');
        });

        // Assert that only the expected routes exist
        $customerRoutes->each(function ($route) use ($expectedRouteNames) {
            $this->assertTrue(in_array($route->getName(), $expectedRouteNames),
                "Route {$route->getName()} does not match expected routes.");

        });
        // Assert that there are routes found for /item/category/
        $this->assertFalse($customerRoutes->isEmpty(), 'No routes found for /item');

    });

});
describe('Item Create', function () {

    beforeEach(function () {

        $this->notAdmin = User::factory()->worker()->create(['email' => 'not_admin@admin.com']);
        $this->admin = User::factory()->admin()->create(['email' => 'admin@admin.com']);
        $this->resourceCategory = ItemCategory::factory()->create();

    });
    it('should prevent non auth creating new Item', function () {
        $response = postJson('/api/v1/item/create');
        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    });
    it('should prevent non admin creating new Item', function () {

        $response = actingAs($this->notAdmin)->postJson('/api/v1/item/create');
        $response->assertStatus(Response::HTTP_FORBIDDEN);
    });
    it('should return validation error when data is missed', function () {
        $response = actingAs($this->admin)->postJson('/api/v1/item/create', []);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    });
    it('should create new Item with valid data', function () {
        $response = actingAs($this->admin)->postJson('/api/v1/item/create', [
            'name' => 'new',
            'description' => 'new',
            'item_category_id' => $this->resourceCategory->id,
        ]);
        $response->assertOk();
    });
});
describe('Item Update', function () {

    beforeEach(function () {

        $this->notAdmin = User::factory()->worker()->create(['email' => 'not_admin@admin.com']);
        $this->admin = User::factory()->admin()->create(['email' => 'admin@admin.com']);

        $this->item = \App\Models\Item::factory()->create(['name' => 'new']);

    });

    it('should prevent non auth updating existed Item', function () {
        $response = putJson('/api/v1/item/update/'.$this->item->id, [
            'name' => 'new1',
        ]);
        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    });
    it('should prevent non admin updating existed Item', function () {

        $response = actingAs($this->notAdmin)->putJson('/api/v1/item/update/'.$this->item->id, [
            'name' => 'new1',
        ]);
        $response->assertStatus(Response::HTTP_FORBIDDEN);
    });
    it('should not return validation error when data is missed', function () {
        $response = actingAs($this->admin)->postJson('/api/v1/item/create', []);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    });
    it('should update existed Item with valid data', function () {

        $response = actingAs($this->admin)->putJson('/api/v1/item/update/'.$this->item->id, [
            'name' => 'new1',
        ]);
        assertDatabaseHas('items', ['name' => 'new1']);
        $response->assertOk();
    });
});
describe('Item List', function () {

    beforeEach(function () {

        $this->notAdmin = User::factory()->worker()->create(['email' => 'not_admin@admin.com']);
        $this->admin = User::factory()->admin()->create(['email' => 'admin@admin.com']);

        $this->resource1 = Item::factory()->create(['name' => 'item 1']);
        $this->resource2 = Item::factory()->create(['name' => 'item 2']);
        $this->resource3 = Item::factory()->create(['name' => 'item 3']);

    });
    it('should prevent non auth updating existed item', function () {
        $response = getJson('/api/v1/item/list/');
        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    });
    it('should return data', function () {
        $response = actingAs($this->admin)->getJson('/api/v1/item/list/');
        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonFragment([
                'name' => 'item 1',
            ]);
    });
});
describe('Item Details', function () {

    beforeEach(function () {

        $this->notAdmin = User::factory()->worker()->create(['email' => 'not_admin@admin.com']);
        $this->admin = User::factory()->admin()->create(['email' => 'admin@admin.com']);

        $this->item = Item::factory()->create(['id' => 10, 'name' => 'item 10']);

    });
    it('should prevent non auth show item', function () {
        $response = getJson('/api/v1/item/show/'.$this->item->id);
        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    });
    it('should return not found for un-existed item', function () {
        $response = actingAs($this->admin)->getJson('/api/v1/item/show/2');
        $response->assertStatus(Response::HTTP_NOT_FOUND);
    });
    it('should return data', function () {
        $response = actingAs($this->admin)->getJson('/api/v1/item/show/'.$this->item->id);
        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonFragment(['name' => 'item 10']);
    });
});
describe('Item Delete', function () {

    beforeEach(function () {

        $this->notAdmin = User::factory()->worker()->create(['email' => 'not_admin@admin.com']);
        $this->admin = User::factory()->admin()->create(['email' => 'admin@admin.com']);

        $this->item = Item::factory()->create(['id' => 10, 'name' => 'item 10']);

    });
    it('should prevent non auth delete item', function () {
        $response = deleteJson('/api/v1/item/delete/'.$this->item->id);
        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    });
    it('should prevent non admin delete existed item', function () {
        $response = actingAs($this->notAdmin)->deleteJson('/api/v1/item/delete/'.$this->item->id);
        $response->assertStatus(Response::HTTP_FORBIDDEN);
    });
    it('should return not found for un-existed item', function () {
        $response = actingAs($this->admin)->deleteJson('/api/v1/item/delete/2');
        $response->assertStatus(Response::HTTP_NOT_FOUND);
    });
    it('should delete item from database', function () {
        assertDatabaseCount(Item::class, 1);
        actingAs($this->admin)->deleteJson('/api/v1/item/delete/'.$this->item->id);
        assertSoftDeleted('items', ['id' => $this->item->id]);
    });
});

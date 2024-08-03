<?php

use App\Models\Address;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WorkSite;
use Symfony\Component\HttpFoundation\Response;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertSoftDeleted;

describe('Warehouse routes check', function () {
    it('should have all routes for /warehouse', function () {
        $this->artisan('optimize:clear');
        // Define the expected route names
        $expectedRouteNames = [
            'warehouse.create',
            'warehouse.update',
            'warehouse.list',
            'warehouse.show',
            'warehouse.delete',
            'warehouse.item.create',
            'warehouse.item.move',
            'warehouse.item.update',
            'warehouse.item.list',
        ];

        // Collect routes and filter based on the prefix
        $warehouseRoutes = collect(Route::getRoutes())->filter(function ($route) {
            return str_starts_with($route->uri, 'api/v1/warehouse/');
        });

        // Assert that only the expected routes exist
        $warehouseRoutes->each(function ($route) use ($expectedRouteNames) {
            $this->assertTrue(in_array($route->getName(), $expectedRouteNames),
                "Route {$route->getName()} does not match expected routes.");

        });
        // Assert that there are routes found for /warehouse
        $this->assertFalse($warehouseRoutes->isEmpty(), 'No routes found for /warehouse');

    });

});
describe('Warehouse Create Test', function () {

    beforeEach(function () {

        $this->workSite = WorkSite::factory()->create();
        $this->admin = User::factory()->admin()->create();
        $this->notAdmin = User::factory()->worker()->create();
        $this->address = Address::factory()->create();

    });

    it('should create main warehouse in a specific location', function () {
        actingAs($this->admin)->postJson('/api/v1/warehouse/store', [
            'name' => 'Main Warehouse',
            'address_id' => $this->address->id,
        ])
            ->assertStatus(Response::HTTP_OK);
        assertDatabaseHas(Warehouse::class, [
            'name' => 'Main Warehouse',
            'address_id' => $this->address->id,
        ]);
    });
});
describe('Warehouse Update Test', function () {
    beforeEach(function () {

        $this->workSite = WorkSite::factory()->create();
        $this->admin = User::factory()->admin()->create();
        $this->notAdmin = User::factory()->worker()->create();
        $this->address = Address::factory()->create();

    });

    it('should update a warehouse', function () {
        $wareHouse = Warehouse::factory()->create([
            'name' => 'Main Warehouse',
            'address_id' => $this->address->id,
        ]);
        $newAddress = Address::factory()->create();
        actingAs($this->admin)->putJson('/api/v1/warehouse/update/'.$wareHouse->id, [
            'name' => 'Main Warehouse Updated',
            'address_id' => $newAddress->id,
        ])->assertStatus(Response::HTTP_OK);
        assertDatabaseHas(Warehouse::class, [
            'name' => 'Main Warehouse Updated',
            'address_id' => $newAddress->id,
        ]);
    });
});
describe('Warehouse List Test', function () {

    beforeEach(function () {

        $this->workSite = WorkSite::factory()->create();
        $this->admin = User::factory()->admin()->create();
        $this->notAdmin = User::factory()->worker()->create();
        $this->address = Address::factory()->create();
        $this->otherAddress = Address::factory()->create();
        $this->firstWarehouse = Warehouse::factory()->create([
            'name' => 'Main Warehouse',
            'address_id' => $this->address->id,
        ]);
        $this->secondWarehouse = Warehouse::factory()->create([
            'name' => 'Second Warehouse',
            'address_id' => $this->otherAddress->id,
        ]);

    });
    it('should get a list of warehouses', function () {
        actingAs($this->admin)->getJson('/api/v1/warehouse/list')
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonFragment([
                'name' => 'Main Warehouse',
                'address' => [
                    'id' => $this->address->id,
                    'city' => $this->address->city->name,
                    'state' => $this->address->state,
                    'street' => $this->address->street,
                    'zipCode' => $this->address->zipcode,
                    'title' => $this->address->title,
                ],
            ])
            ->assertJsonFragment([
                'name' => 'Second Warehouse',
                'address' => [
                    'id' => $this->otherAddress->id,
                    'city' => $this->otherAddress->city->name,
                    'state' => $this->otherAddress->state,
                    'street' => $this->otherAddress->street,
                    'zipCode' => $this->otherAddress->zipcode,
                    'title' => $this->otherAddress->title,
                ],
            ]);
        assertDatabaseCount(Warehouse::class, 2);
    });
});
describe('Warehouse Details Test', function () {

    beforeEach(function () {

        $this->workSite = WorkSite::factory()->create();
        $this->admin = User::factory()->admin()->create();
        $this->notAdmin = User::factory()->worker()->create();
        $this->address = Address::factory()->create();

        $this->warehouse = Warehouse::factory()->create([
            'name' => 'Main Warehouse',
            'address_id' => $this->address->id,
        ]);

    });
    it('should return not found error if warehouse nof found', function () {
        $unExistedWarehouseId = rand(2222, 3333);
        actingAs($this->admin)->getJson('/api/v1/warehouse/show/'.$unExistedWarehouseId)
            ->assertStatus(Response::HTTP_NOT_FOUND);
    });
    it('should get a warehouse details', function () {
        actingAs($this->admin)->getJson('/api/v1/warehouse/show/'.$this->warehouse->id)
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonFragment([
                'name' => 'Main Warehouse',
                'address' => [
                    'id' => $this->address->id,
                    'city' => $this->address->city->name,
                    'state' => $this->address->state,
                    'street' => $this->address->street,
                    'zipCode' => $this->address->zipcode,
                    'title' => $this->address->title,
                ],
            ]);
    });
});
describe('Warehouse Delete Test', function () {

    beforeEach(function () {

        $this->workSite = WorkSite::factory()->create();
        $this->admin = User::factory()->admin()->create();
        $this->notAdmin = User::factory()->worker()->create();
        $this->address = Address::factory()->create();
        $this->wareHouse = Warehouse::factory()->create();

    });
    it('should return not found error if warehouse nof found', function () {
        $unExistedWarehouseId = rand(22, 33);
        actingAs($this->admin)->deleteJson('/api/v1/warehouse/delete/'.$unExistedWarehouseId)
            ->assertStatus(Response::HTTP_NOT_FOUND);
    });
    it('should delete a warehouse', function () {
        actingAs($this->admin)->deleteJson('/api/v1/warehouse/delete/'.$this->wareHouse->id)
            ->assertStatus(Response::HTTP_OK);
        assertSoftDeleted(Warehouse::class, ['id' => $this->wareHouse->id]);
    });
});

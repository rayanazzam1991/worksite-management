<?php

use App\Models\Address;
use App\Models\Contractor;
use App\Models\User;
use Symfony\Component\HttpFoundation\Response;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\postJson;

describe('Contractor routes check', function () {
    it('should have all routes for /contractor', function () {
        $this->artisan('optimize:clear');
        // Define the expected route names
        $expectedRouteNames = [
            'contractor.create',
            'contractor.update',
            'contractor.list',
            'contractor.show',
            'contractor.delete',
        ];

        // Collect routes and filter based on the prefix
        $contractorRoutes = collect(Route::getRoutes())->filter(function ($route) {
            return str_starts_with($route->uri, 'api/v1/contractor/');
        });

        // Assert that only the expected routes exist
        $contractorRoutes->each(function ($route) use ($expectedRouteNames) {
            $this->assertTrue(in_array($route->getName(), $expectedRouteNames),
                "Route {$route->getName()} does not match expected routes.");

        });
        // Assert that there are routes found for /contractor
        $this->assertFalse($contractorRoutes->isEmpty(), 'No routes found for /contractor');

    });

});
describe('Create Contractor of workSite', function () {

    beforeEach(function () {
        $this->notAdmin = User::factory()->worker()->create(['email' => 'not_admin@admin.com']);
        $this->admin = User::factory()->admin()->create(['email' => 'admin@admin.com']);
    });
    it('should prevent non auth creating new Contractor', function () {
        $response = postJson('/api/v1/contractor/create');
        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    });
    it('should prevent non admin creating new Contractor', function () {
        $response = actingAs($this->notAdmin)->postJson('/api/v1/contractor/create');
        $response->assertStatus(Response::HTTP_FORBIDDEN);
    });
    it('should return validation error when data is missed', function () {
        $response = actingAs($this->admin)->postJson('/api/v1/contractor/create', []);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    });
    it('should create new Contractor with valid data', function () {
        $address = Address::factory()->create();
        $response = actingAs($this->admin)->postJson('/api/v1/contractor/create', [
            'first_name' => 'Rayan',
            'last_name' => 'Azzam',
            'address_id' => $address->id,
        ]);
        assertDatabaseHas(Contractor::class, [
            'first_name' => 'Rayan',
            'last_name' => 'Azzam',
            'address_id' => $address->id,
        ]);
        $response->assertOk();
    });

});
describe('Update Contractor of workSite', function () {

    beforeEach(function () {
        $this->notAdmin = User::factory()->worker()->create(['email' => 'not_admin@admin.com']);
        $this->admin = User::factory()->admin()->create(['email' => 'admin@admin.com']);

        $this->address = Address::factory()->create();
        $this->contractor = Contractor::factory()->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'address_id' => $this->address->id,
            'phone' => '0945795748',
        ]);
    });
    it('should prevent non auth updating new Contractor', function () {
        $response = $this->putJson('/api/v1/contractor/update/'.$this->contractor->id);
        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    });
    it('should prevent non admin updating new Contractor', function () {
        $response = actingAs($this->notAdmin)->putJson('/api/v1/contractor/update/'.$this->contractor->id);
        $response->assertStatus(Response::HTTP_FORBIDDEN);
    });
    it('should return not found if updating non-existed contractor', function () {
        $unExisted = rand(33, 44);
        $response = actingAs($this->notAdmin)->putJson('/api/v1/contractor/update/'.$unExisted);
        $response->assertStatus(Response::HTTP_FORBIDDEN);
    });
    it('should update existed Contractor with valid data', function () {
        $addressNew = Address::factory()->create();
        $response = actingAs($this->admin)->putJson('/api/v1/contractor/update/'.$this->contractor->id, [
            'first_name' => 'Rayan',
            'phone' => '0945795749',
            'address_id' => $addressNew->id,
        ]);
        assertDatabaseHas(Contractor::class, [
            'first_name' => 'Rayan',
            'last_name' => 'Doe',
            'phone' => '0945795749',
            'address_id' => $addressNew->id,
        ]);
        $response->assertOk();
    });

});
describe('List of Contractors Test', function () {

    beforeEach(function () {
        $this->notAdmin = User::factory()->worker()->create();
        $this->admin = User::factory()->admin()->create();

        $this->address = Address::factory()->create();
        $this->contractor = Contractor::factory(10)->create();
        assertDatabaseCount(Contractor::class, 10);
    });
    it('should prevent non auth show list of Contractors', function () {
        $response = $this->getJson('/api/v1/contractor/list');
        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    });
    it('should prevent non admin show list of Contractors', function () {
        $response = actingAs($this->notAdmin)->getJson('/api/v1/contractor/list');
        $response->assertStatus(Response::HTTP_FORBIDDEN);
    });

    it('should return list of contractors', function () {
        $response = actingAs($this->admin)->getJson('/api/v1/contractor/list');
        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    [
                        'id',
                        'first_name',
                        'last_name',
                        'phone',
                        'address',
                    ],
                ],
            ]);
    });

});
describe('Details of Contractors Test', function () {

    beforeEach(function () {
        $this->notAdmin = User::factory()->worker()->create();
        $this->admin = User::factory()->admin()->create();

        $this->address = Address::factory()->create();
        $this->contractor = Contractor::factory()->create([
            'first_name' => 'Rayan',
            'last_name' => 'Azzam',
            'phone' => '0945795748',
            'address_id' => $this->address->id,
        ]);
    });
    it('should prevent non auth show details of a Contractor', function () {
        $response = $this->getJson('/api/v1/contractor/show/'.$this->contractor->id);
        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    });
    it('should prevent non admin show details of a Contractor', function () {
        $response = actingAs($this->notAdmin)->getJson('/api/v1/contractor/show/'.$this->contractor->id);
        $response->assertStatus(Response::HTTP_FORBIDDEN);
    });
    it('should return not found if show non-existed contractor', function () {
        $unExisted = rand(33, 44);
        $response = actingAs($this->notAdmin)->getJson('/api/v1/contractor/show/'.$unExisted);
        $response->assertStatus(Response::HTTP_FORBIDDEN);
    });
    it('should show details of a contractor', function () {
        $response = actingAs($this->admin)->getJson('/api/v1/contractor/show/'.$this->contractor->id);
        $response->assertOk()
            ->assertJsonFragment([
                'first_name' => $this->contractor->first_name,
                'last_name' => $this->contractor->last_name,
                'phone' => $this->contractor->phone,
                'address' => [
                    'id' => $this->address->id,
                    'title' => $this->address->title,
                    'city' => $this->address->city?->name,
                    'street' => $this->address->street,
                    'state' => $this->address->state,
                    'zipCode' => $this->address->zipcode,
                ],
            ]);
    });

});

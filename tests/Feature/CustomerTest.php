<?php

use App\Enums\PaymentTypesEnum;
use App\Enums\WorkSiteCompletionStatusEnum;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\User;
use App\Models\WorkSite;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\Response;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertSoftDeleted;
use function Pest\Laravel\deleteJson;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;
use function Pest\Laravel\putJson;

describe('Customer routes check', function () {
    it('should have all routes for /customer', function () {
        $this->artisan('optimize:clear');
        // Define the expected route names
        $expectedRouteNames = [
            'customer.create',
            'customer.update',
            'customer.list',
            'customer.show',
            'customer.delete',
        ];

        // Collect routes and filter based on the prefix
        $customerRoutes = collect(Route::getRoutes())->filter(function ($route) {
            return str_starts_with($route->uri, 'api/v1/customer/');
        });

        // Assert that only the expected routes exist
        $customerRoutes->each(function ($route) use ($expectedRouteNames) {
            $this->assertTrue(in_array($route->getName(), $expectedRouteNames),
                "Route {$route->getName()} does not match expected routes.");

        });
        // Assert that there are routes found for /customer
        $this->assertFalse($customerRoutes->isEmpty(), 'No routes found for /customer');

    });

});
describe('Customer Create', function () {

    beforeEach(function () {

        $this->notAdmin = User::factory()->worker()->create(['email' => 'not_admin@admin.com']);
        $this->admin = User::factory()->admin()->create(['email' => 'admin@admin.com']);
    });

    it('should prevent non auth creating new Customer', function () {
        $response = postJson('/api/v1/customer/create');
        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    });
    it('should prevent non admin creating new Customer', function () {

        $response = actingAs($this->notAdmin)->postJson('/api/v1/customer/create');
        $response->assertStatus(Response::HTTP_FORBIDDEN);
    });
    it('should return validation error when data is missed', function () {
        $response = actingAs($this->admin)->postJson('/api/v1/customer/create', []);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    });
    it('should create new Customer with valid data', function () {
        $response = actingAs($this->admin)->postJson('/api/v1/customer/create', [
            'first_name' => 'Rayan',
            'last_name' => 'Azzam',
        ]);
        $response->assertOk();
    });
});
describe('Customer Update', function () {

    beforeEach(function () {

        $this->notAdmin = User::factory()->worker()->create(['email' => 'not_admin@admin.com']);
        $this->admin = User::factory()->admin()->create(['email' => 'admin@admin.com']);

        $this->customer = Customer::factory()->create(['first_name' => 'Rayan']);
    });

    it('should prevent non auth updating a Customer', function () {
        $response = putJson('/api/v1/customer/update/'.$this->customer->id);
        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    });
    it('should prevent non admin updating a Customer', function () {
        $response = actingAs($this->notAdmin)->putJson('/api/v1/customer/update/'.$this->customer->id);
        $response->assertStatus(Response::HTTP_FORBIDDEN);
    });
    it('should not return validation error when data is missed', function () {
        $response = actingAs($this->admin)->putJson('/api/v1/customer/update/'.$this->customer->id, []);
        $response->assertStatus(Response::HTTP_OK);
    });
    it('should not touch a field if not updated', function () {
        $response = actingAs($this->admin)->putJson('/api/v1/customer/update/'.$this->customer->id, []);
        assertDatabaseHas('customers', ['first_name' => 'Rayan']);
        $response->assertStatus(Response::HTTP_OK);
    });
    it('should create new Customer with valid data', function () {
        $response = actingAs($this->admin)->putJson('/api/v1/customer/update/'.$this->customer->id, [
            'first_name' => 'John',
        ]);
        assertDatabaseHas('customers', ['first_name' => 'John']);
        $response->assertOk();
    });
});
describe('Customers List', function () {

    beforeEach(function () {

        $this->notAdmin = User::factory()->worker()->create(['email' => 'not_admin@admin.com']);
        $this->admin = User::factory()->admin()->create(['email' => 'admin@admin.com']);

        $this->customers = Customer::factory(10)->create();
    });

    it('should prevent non auth show list of Customers', function () {
        $response = getJson('/api/v1/customer/list/');
        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    });
    it('should prevent non admin show list of Customers', function () {
        $response = actingAs($this->notAdmin)->getJson('/api/v1/customer/list/');
        $response->assertStatus(Response::HTTP_FORBIDDEN);
    });
    it('should show list of Customers', function () {
        actingAs($this->admin)->getJson('/api/v1/customer/list/')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'first_name',
                        'last_name',
                        'phone',
                        'address',
                    ],
                ],
            ]);
        assertDatabaseCount(Customer::class, 10);
    });
});
describe('Customer Details', function () {

    beforeEach(function () {

        $this->notAdmin = User::factory()->worker()->create(['email' => 'not_admin@admin.com']);
        $this->admin = User::factory()->admin()->create(['email' => 'admin@admin.com']);

        $this->customer = Customer::factory()->create();
    });

    it('should prevent non auth show details of a Customer', function () {
        $response = getJson('/api/v1/customer/show/'.$this->customer->id);
        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    });
    it('should prevent non admin show details of a Customer', function () {
        $response = actingAs($this->notAdmin)->getJson('/api/v1/customer/show/'.$this->customer->id);
        $response->assertStatus(Response::HTTP_FORBIDDEN);
    });
    it('should show details of a Customer', function () {
        $payment = Payment::factory()->create([
            'payable_id' => $this->customer->id,
            'payable_type' => 'customer',
            'amount' => 200,
            'payment_date' => Carbon::now(),
            'payment_type' => PaymentTypesEnum::CASH->value,
        ]);
        actingAs($this->admin)->getJson('/api/v1/customer/show/'.$this->customer->id)
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'first_name',
                    'last_name',
                    'phone',
                    'address',
                    'payments',
                ],
            ])
            ->assertJsonFragment([
                'first_name' => $this->customer->first_name,
                'last_name' => $this->customer->last_name,
                'phone' => $this->customer->phone,
                'address' => $this->customer->address,
                'payments' => [
                    [
                        'id' => $payment->id,
                        'payable_id' => $payment->payable_id,
                        'payable_type' => $payment->payable_type,
                        'payment_type' => PaymentTypesEnum::CASH->value,
                        'amount' => number_format($payment->amount, 2),
                        'date' => Carbon::parse($payment->payment_date)->format('Y-m-d H:i:s'),
                    ],
                ],
            ]);
    });
});
describe('Customer Delete', function () {

    beforeEach(function () {

        $this->notAdmin = User::factory()->worker()->create(['email' => 'not_admin@admin.com']);
        $this->admin = User::factory()->admin()->create(['email' => 'admin@admin.com']);

        $this->customer = Customer::factory()->create();
    });

    it('should prevent non auth delete a Customer', function () {
        $response = deleteJson('/api/v1/customer/delete/'.$this->customer->id);
        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    });
    it('should prevent non admin delete a Customer', function () {
        $response = actingAs($this->notAdmin)->deleteJson('/api/v1/customer/delete/'.$this->customer->id);
        $response->assertStatus(Response::HTTP_FORBIDDEN);
    });
    it('should prevent delete a Customer related to un closed worksite', function () {
        $worksite = Worksite::factory()->create([
            'customer_id' => $this->customer->id,
            'completion_status' => WorkSiteCompletionStatusEnum::STARTED->value,
        ]);
        $response = actingAs($this->admin)->deleteJson('/api/v1/customer/delete/'.$this->customer->id);
        $response->assertStatus(Response::HTTP_CONFLICT)
            ->assertJsonFragment([
                'message' => 'Unable to delete customer with a not closed work site',
            ]);
    });
    it('should delete a Customer', function () {
        actingAs($this->admin)->deleteJson('/api/v1/customer/delete/'.$this->customer->id)
            ->assertOk();
        assertSoftDeleted(Customer::class, ['id' => $this->customer->id]);
    });

});

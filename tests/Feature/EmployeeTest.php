<?php

use App\Models\User;
use Symfony\Component\HttpFoundation\Response;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertSoftDeleted;
use function Pest\Laravel\postJson;

describe('Employee routes check', function () {
    it('should have all routes for /employee', function () {
        $this->artisan('optimize:clear');
        // Define the expected route names
        $expectedRouteNames = [
            'employee.create',
            'employee.update',
            'employee.list',
            'employee.show',
            'employee.delete',

            'employee.dailyAttendance.add',
            'employee.dailyAttendance.list',
            'employee.dailyAttendance.update',
        ];

        // Collect routes and filter based on the prefix
        $customerRoutes = collect(Route::getRoutes())->filter(function ($route) {
            return str_starts_with($route->uri, 'api/v1/employee/');
        });

        // Assert that only the expected routes exist
        $customerRoutes->each(function ($route) use ($expectedRouteNames) {
            $this->assertTrue(in_array($route->getName(), $expectedRouteNames),
                "Route {$route->getName()} does not match expected routes.");

        });
        // Assert that there are routes found for /customer
        $this->assertFalse($customerRoutes->isEmpty(), 'No routes found for /employee');

    });

});

describe('Create Employee', function () {

    beforeEach(function () {

        $this->notAdmin = User::factory()->worker()->create(['email' => 'not_admin@admin.com']);
        $this->admin = User::factory()->admin()->create(['email' => 'admin@admin.com']);
    });

    it('should prevent non auth creating new Employee', function () {
        $response = postJson('/api/v1/employee/create');
        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    });
    it('should prevent non admin creating new Employee', function () {

        $response = actingAs($this->notAdmin)->postJson('/api/v1/employee/create');
        $response->assertStatus(Response::HTTP_FORBIDDEN);
    });
    it('should return validation error when data is missed', function () {
        $response = actingAs($this->admin)->postJson('/api/v1/employee/create', []);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    });
    it('should create new Employee with valid data', function () {
        $response = actingAs($this->admin)->postJson('/api/v1/employee/create', [
            'first_name' => 'Rayan',
            'phone' => '0945795748',
        ]);
        $response->assertOk();
        assertDatabaseHas(User::class, ['first_name' => 'Rayan']);
    });
});
describe('Update Employee', function () {

    beforeEach(function () {

        $this->notAdmin = User::factory()->worker()->create(['email' => 'not_admin@admin.com']);
        $this->admin = User::factory()->admin()->create(['email' => 'admin@admin.com']);

        $this->employee = User::factory()->create(['first_name' => 'Rayan']);
    });

    it('should prevent non auth updating a Employee', function () {
        $response = $this->putJson('/api/v1/employee/update/'.$this->employee->id);
        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    });
    it('should prevent non admin updating a Employee', function () {

        $response = actingAs($this->notAdmin)->putJson('/api/v1/employee/update/'.$this->employee->id);
        $response->assertStatus(Response::HTTP_FORBIDDEN);
    });
    it('should update a Employee with valid data', function () {
        $response = actingAs($this->admin)->putJson('/api/v1/employee/update/'.$this->employee->id, [
            'first_name' => 'Komay',
        ]);
        $response->assertOk();
        assertDatabaseHas(User::class, ['first_name' => 'Komay']);
    });
});
describe('Show Employees list', function () {

    beforeEach(function () {

        $this->notAdmin = User::factory()->worker()->create(['email' => 'not_admin@admin.com']);
        $this->admin = User::factory()->admin()->create(['email' => 'admin@admin.com']);

    });
    it('should prevent non auth show list of Employees', function () {
        $response = $this->getJson('/api/v1/employee/list');
        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    });
    it('should prevent non admin show list of Employees', function () {
        $response = actingAs($this->notAdmin)->getJson('/api/v1/employee/list');
        $response->assertStatus(Response::HTTP_FORBIDDEN);
    });
    it('should return right number of Employees in database', function () {
        $alreadyExistsUsers = User::count();
        User::factory(10)->create(['first_name' => 'Rayan']);

        $response = actingAs($this->admin)->getJson('/api/v1/employee/list');
        $response->assertStatus(Response::HTTP_OK);
        assertDatabaseCount(User::class, $alreadyExistsUsers + 10);
    });
    it('should return list of employees', function () {
        User::factory()->create(['first_name' => 'Rayan']);
        $response = actingAs($this->admin)->getJson('/api/v1/employee/list');
        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonFragment(['first_name' => 'Rayan']);
    });
});
describe('Show Employee details', function () {

    beforeEach(function () {

        $this->notAdmin = User::factory()->worker()->create();
        $this->admin = User::factory()->admin()->create();

        $this->employee = User::factory()->create(['first_name' => 'Rayan']);
    });
    it('should prevent non auth show details of a Employee', function () {
        $response = $this->getJson('/api/v1/employee/show/'.$this->employee->id);
        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    });
    it('should prevent non admin show details of a Employee', function () {
        $response = actingAs($this->notAdmin)->getJson('/api/v1/employee/show/'.$this->employee->id);
        $response->assertStatus(Response::HTTP_FORBIDDEN);
    });
    it('should return error if employee not existed', function () {
        $nonExistedWorker = rand(1000, 2000);
        $response = actingAs($this->admin)->getJson('/api/v1/employee/show/'.$nonExistedWorker);
        $response->assertStatus(Response::HTTP_NOT_FOUND);
    });
    it('should return details of a Employee', function () {
        $response = actingAs($this->admin)->getJson('/api/v1/employee/show/'.$this->employee->id);
        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonFragment(['first_name' => 'Rayan']);
    });
});
describe('Delete Employee', function () {

    beforeEach(function () {

        $this->notAdmin = User::factory()->worker()->create(['email' => 'not_admin@admin.com']);
        $this->admin = User::factory()->admin()->create(['email' => 'admin@admin.com']);

        $this->employee = User::factory()->create();
    });
    it('should prevent non auth delete a Employee', function () {
        $response = $this->deleteJson('/api/v1/employee/delete/'.$this->employee->id);
        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    });
    it('should prevent non admin delete a Employee', function () {
        $response = actingAs($this->notAdmin)->deleteJson('/api/v1/employee/delete/'.$this->employee->id);
        $response->assertStatus(Response::HTTP_FORBIDDEN);
    });
    it('should return error if employee not existed', function () {
        $nonExistedWorker = rand(1000, 2000);
        $response = actingAs($this->admin)->deleteJson('/api/v1/employee/delete/'.$nonExistedWorker);
        $response->assertStatus(Response::HTTP_NOT_FOUND);
    });
    it('should delete a employee', function () {
        $alreadyExistsUsers = User::count();
        $response = actingAs($this->admin)->deleteJson('/api/v1/employee/delete/'.$this->employee->id);
        $response->assertStatus(Response::HTTP_OK);
        assertDatabaseCount(User::class, $alreadyExistsUsers);
        assertSoftDeleted(User::class, ['id' => $this->employee->id]);
    });
});

<?php

use App\Models\DailyAttendance;
use App\Models\User;
use App\Models\WorkSite;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\Response;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;

describe('DailyAttendance routes check', function () {
    it('should have all routes for /daily_attendance', function () {
        $this->artisan('optimize:clear');
        // Define the expected route names
        $expectedRouteNames = [
            'employee.dailyAttendance.add',
            'employee.dailyAttendance.list',
            'employee.dailyAttendance.update',
        ];

        // Collect routes and filter based on the prefix
        $contractorRoutes = collect(Route::getRoutes())->filter(function ($route) {
            return str_starts_with($route->uri, 'api/v1/employee/{employeeId}/daily_attendance/');
        });

        // Assert that only the expected routes exist
        $contractorRoutes->each(function ($route) use ($expectedRouteNames) {
            $this->assertTrue(in_array($route->getName(), $expectedRouteNames),
                "Route {$route->getName()} does not match expected routes.");

        });
        // Assert that there are routes found for /contractor
        $this->assertFalse($contractorRoutes->isEmpty(), 'No routes found for /employee');

    });

});
describe('EmployeeDailyAttendance Create', function () {

    beforeEach(function () {

        $this->worker = User::factory()->worker()->create();
        $this->admin = User::factory()->admin()->create();
        $this->workSite = WorkSite::factory()->create();
        $this->subWorkSite = WorkSite::factory()->create([
            'parent_work_site_id' => $this->workSite->id,
        ]);
    });

    it('should prevent adding attendance for an employee for a sub-workSite', function () {
        $response = actingAs($this->admin)->postJson('api/v1/employee/'.$this->worker->id.'/daily_attendance/create', [
            'work_site_id' => $this->subWorkSite->id,
        ]);
        $response->assertStatus(Response::HTTP_FORBIDDEN)
            ->assertJsonFragment([
                'message' => 'Cant Assign employee to work site',
            ]);

        assertDatabaseCount(DailyAttendance::class, 0);
    });
    it('should add attendance for an employee for today if no date added', function () {
        $response = actingAs($this->admin)->postJson('api/v1/employee/'.$this->worker->id.'/daily_attendance/create', [
            'work_site_id' => $this->workSite->id,
        ]);
        $response->assertStatus(Response::HTTP_OK);
        assertDatabaseCount(DailyAttendance::class, 1);
        assertDatabaseHas(DailyAttendance::class,
            [
                'employee_id' => $this->worker->id,
                'date' => Carbon::today()->format('Y-m-d'),
                'work_site_id' => $this->workSite->id,
            ],
        );
    });
    it('should add attendance for an employee for a date range', function () {
        $response = actingAs($this->admin)->postJson('api/v1/employee/'.$this->worker->id.'/daily_attendance/create', [
            'date_from' => '2024-01-01',
            'date_to' => '2024-01-02',
            'work_site_id' => $this->workSite->id,
        ]);
        $response->assertStatus(Response::HTTP_OK);
        assertDatabaseCount(DailyAttendance::class, 2);
        assertDatabaseHas(DailyAttendance::class,
            [
                'employee_id' => $this->worker->id,
                'date' => '2024-01-01',
                'work_site_id' => $this->workSite->id,

            ],
        );
        assertDatabaseHas(DailyAttendance::class,
            [
                'employee_id' => $this->worker->id,
                'date' => '2024-01-02',
                'work_site_id' => $this->workSite->id,
            ],
        );
    });
    it('should prevent assigning an employee to same or multiple workSites in a same day', function () {
        $otherWorkSite = WorkSite::factory()->create();

        actingAs($this->admin)->postJson('api/v1/employee/'.$this->worker->id.'/daily_attendance/create', [
            'work_site_id' => $this->workSite->id,
        ])->assertStatus(Response::HTTP_OK);

        actingAs($this->admin)->postJson('api/v1/employee/'.$this->worker->id.'/daily_attendance/create', [
            'work_site_id' => $this->workSite->id,
        ])->assertStatus(Response::HTTP_FORBIDDEN);

        actingAs($this->admin)->postJson('api/v1/employee/'.$this->worker->id.'/daily_attendance/create', [
            'work_site_id' => $otherWorkSite->id,
        ])->assertStatus(Response::HTTP_FORBIDDEN);

        assertDatabaseCount(DailyAttendance::class, 1);
        assertDatabaseHas(DailyAttendance::class,
            [
                'employee_id' => $this->worker->id,
                'date' => Carbon::today()->format('Y-m-d'),
                'work_site_id' => $this->workSite->id,
            ],
        );
        assertDatabaseMissing(DailyAttendance::class, [
            'employee_id' => $this->worker->id,
            'date' => Carbon::today()->format('Y-m-d'),
            'work_site_id' => $otherWorkSite->id,
        ]);
    });
    it('should prevent assigning an employee to same or multiple workSites in for date range', function () {
        $otherWorkSite = WorkSite::factory()->create();

        actingAs($this->admin)->postJson('api/v1/employee/'.$this->worker->id.'/daily_attendance/create', [
            'work_site_id' => $this->workSite->id,
        ])->assertStatus(Response::HTTP_OK);

        actingAs($this->admin)->postJson('api/v1/employee/'.$this->worker->id.'/daily_attendance/create', [
            'work_site_id' => $otherWorkSite->id,
            'date_from' => Carbon::today()->subDay(2)->format('Y-m-d'),
            'date_to' => Carbon::today()->addDays(2)->format('Y-m-d'),
        ])->assertStatus(Response::HTTP_FORBIDDEN);

        assertDatabaseCount(DailyAttendance::class, 1);
        assertDatabaseHas(DailyAttendance::class,
            [
                'employee_id' => $this->worker->id,
                'date' => Carbon::today()->format('Y-m-d'),
                'work_site_id' => $this->workSite->id,
            ],
        );
        assertDatabaseMissing(DailyAttendance::class, [
            'employee_id' => $this->worker->id,
            'date' => Carbon::today()->format('Y-m-d'),
            'work_site_id' => $otherWorkSite->id,
        ]);
    });
});
describe('EmployeeDailyAttendance Update', function () {

    beforeEach(function () {

        $this->worker = User::factory()->worker()->create();
        $this->admin = User::factory()->admin()->create();
        $this->workSite1 = WorkSite::factory()->create();
        $this->workSite2 = WorkSite::factory()->create();
        $this->subWorkSite = WorkSite::factory()->create([
            'parent_work_site_id' => $this->workSite1->id,
        ]);
        $this->employeeAttendance = DailyAttendance::factory()->create([
            'work_site_id' => $this->workSite1->id,
            'employee_id' => $this->worker->id,
            'date' => '2024-08-01',
        ]);
    });

    it('should update attendance for an employee', function () {
        $response = actingAs($this->admin)
            ->putJson('api/v1/employee/'.$this->worker->id.'/daily_attendance/update/'.$this->employeeAttendance->id, [
                'work_site_id' => $this->workSite2->id,
                'date_from' => '2024-08-02',
                'date_to' => '2024-08-02',
            ]);
        $response->assertStatus(Response::HTTP_OK);
        assertDatabaseHas(DailyAttendance::class,
            [
                'employee_id' => $this->worker->id,
                'date' => '2024-08-02',
                'work_site_id' => $this->workSite2->id,
            ],
        );
    });
    it('should remove attendance to any worksite for this employee in a date', function () {
        $response = actingAs($this->admin)
            ->putJson('api/v1/employee/'.$this->worker->id.'/daily_attendance/update/'.$this->employeeAttendance->id, [
                'date_from' => '2024-08-02',
                'date_to' => '2024-08-02',
            ]);
        $response->assertStatus(Response::HTTP_OK);
        assertDatabaseMissing(DailyAttendance::class,
            [
                'employee_id' => $this->worker->id,
                'date' => '2024-08-02',
                'work_site_id' => $this->workSite1->id,
            ],
        );
    });
});
describe('EmployeeDailyAttendance List', function () {
    beforeEach(function () {

        $this->worker = User::factory()->worker()->create();
        $this->admin = User::factory()->admin()->create();
        $this->workSite = WorkSite::factory()->create();
        DailyAttendance::factory()->create([
            'work_site_id' => $this->workSite->id,
            'employee_id' => $this->worker->id,
            'date' => Carbon::today()->format('Y-m-d'),
        ]);
        DailyAttendance::factory()->create([
            'work_site_id' => $this->workSite->id,
            'employee_id' => $this->worker->id,
            'date' => Carbon::today()->addDay()->format('Y-m-d'),
        ]);
        DailyAttendance::factory()->create([
            'work_site_id' => $this->workSite->id,
            'employee_id' => $this->worker->id,
            'date' => Carbon::today()->addDays(2)->format('Y-m-d'),
        ]);
    });
    it('should list employee daily attendance', function () {
        $dateFrom = Carbon::today()->subDay()->format('Y-m-d');
        $dateTo = Carbon::today()->addDay()->format('Y-m-d');
        $query = '?date_from='.$dateFrom.'&date_to='.$dateTo;
        actingAs($this->admin)->getJson('api/v1/employee/'.$this->worker->id.'/daily_attendance/list'.$query)
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonPath('data', [
                [
                    'workSite' => $this->workSite->title,
                    'date' => Carbon::today()->format('Y-m-d'),
                ],
                [
                    'workSite' => $this->workSite->title,
                    'date' => Carbon::today()->addDay()->format('Y-m-d'),
                ],
            ]);
    });
});

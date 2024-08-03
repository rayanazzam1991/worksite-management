<?php

use App\Enums\PaymentTypesEnum;
use App\Enums\WorkSiteCompletionStatusEnum;
use App\Enums\WorkSiteReceptionStatusEnum;
use App\Models\Address;
use App\Models\Contractor;
use App\Models\Customer;
use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\User;
use App\Models\WorkSite;
use App\Models\WorkSiteCategory;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;
use function Pest\Laravel\putJson;

describe('Worksite entity fields check', function () {
    beforeEach(function () {
        $this->requiredFields = [
            'id',
            'title',
            'description',
        ];
        $this->nullableFields = [
            'customer_id',
            'category_id',
            'parent_work_site_id',
            'contractor_id',
            'starting_budget',
            'cost',
            'address_id',
            'workers_count',
            'receipt_date',
            'starting_date',
            'deliver_date',
            'reception_status',
            'completion_status',
            'created_at',
            'updated_at',
            'deleted_at',
        ];
    });
    it('should have not nullable fields', function () {
        // Get table columns
        $tableColumns = collect(Schema::getColumns('work_sites'));

        $requiredColumns = $tableColumns->filter(function ($item) {
            return ! $item['nullable'];
        })->map(function ($subItem) {
            return $subItem['name'];
        })->toArray();

        $nullableColumns = $tableColumns->filter(function ($item) {
            return $item['nullable'];
        })->map(function ($subItem) {
            return $subItem['name'];
        })->toArray();

        $this->assertEqualsCanonicalizing($requiredColumns, $this->requiredFields);
        $this->assertEqualsCanonicalizing($nullableColumns, $this->nullableFields);

    });

});
describe('WorkSite routes check', function () {
    it('should have all routes for /workSite', function () {
        $this->artisan('optimize:clear');
        // Define the expected route names
        $expectedRouteNames = [
            'workSite.create',
            'workSite.update',
            'workSite.list',
            'workSite.show',
            'workSite.delete',
            'workSite.close',

            'workSite.resource.list',
            'workSite.resource.create',
            'workSite.resource.show',
            'workSite.resource.delete',
            'workSite.resource.update',

            'workSite.payment.create',
            'workSite.payment.list',
            'workSite.payment.show',

            'workSite.category.create',
            'workSite.category.update',
            'workSite.category.list',
            'workSite.category.show',
            'workSite.category.delete',

            'workSite.employee.assign',
            'workSite.contractor.assign',

            'workSite.customer.assign',
            'workSite.customer.update',
            'workSite.customer.show',
            'workSite.customer.delete',

            'workSite.item.add',
            'workSite.item.list',
        ];

        // Collect routes and filter based on the prefix
        $worksiteRoutes = collect(Route::getRoutes())->filter(function ($route) {
            return str_starts_with($route->uri, 'api/v1/workSite/');
        });

        // Assert that only the expected routes exist
        $worksiteRoutes->each(function ($route) use ($expectedRouteNames) {
            $this->assertTrue(in_array($route->getName(), $expectedRouteNames),
                "Route {$route->getName()} does not match expected routes.");

        });
        // Assert that there are routes found for /workSite
        $this->assertFalse($worksiteRoutes->isEmpty(), 'No routes found for /workSite');

    });

});
describe('Create WorkSite', function () {

    beforeEach(function () {

    });

    test('As an administrator, I want to create a main workSite', function () {

        $wsCategory = WorkSiteCategory::factory()->create();

        $customer = Customer::factory()->create();
        $address = Address::factory()->create();
        $contractor = Contractor::factory()->create();

        $workSiteResourceCategory = ItemCategory::factory()->create();

        $workSiteResource1 = Item::factory()->create([
            'item_category_id' => $workSiteResourceCategory->id,
        ]);
        $workSiteResource2 = Item::factory()->create([
            'item_category_id' => $workSiteResourceCategory->id,
        ]);

        $admin = User::factory()->admin()->create();
        expect($admin->hasRole('admin'))->toBe(true);

        Storage::fake();

        $files = UploadedFile::fake()->image('test.jpg');

        $response = $this->actingAs($admin)->postJson('/api/v1/workSite/create', [
            'title' => 'workSite A',
            'description' => 'this workSite is for freeTown',
            'customer_id' => $customer->id,
            'category_id' => $wsCategory->id, // construction
            'contractor_id' => $contractor->id,
            'parent_work_site_id' => null, // this is main workSite == top level workSite
            'starting_budget' => 15,
            'cost' => 20,
            'address_id' => $address?->id,
            'workers_count' => 20,
            'receipt_date' => '2024-04-12',
            'starting_date' => '2024-04-12',
            'deliver_date' => '2024-04-12',
            'reception_status' => WorkSiteReceptionStatusEnum::SCRATCH->value,
            'completion_status' => WorkSiteCompletionStatusEnum::PENDING->value,
            'items' => [
                ['id' => $workSiteResource1?->id, 'quantity' => 23, 'price' => 34],
                ['id' => $workSiteResource2?->id, 'quantity' => 30, 'price' => 30],
            ],
            'payments' => [
                ['payment_amount' => 3000,
                    'payment_date' => '2024-04-12 10:34'],
            ],

            'images' => [$files],

        ]);
        $response->assertOk();

        // Assert the file was stored...
        $path = 'workSite';
        $name = 'test'.'_'.now()->format('YmdH');
        $fullPath = public_path('storage/'.$path).'/'.$name.'.webp';

        $this->assertFileExists($fullPath);

        $workSite = WorkSite::query()->latest('id')->first();

        assertDatabaseHas(WorkSite::class, [
            'reception_status' => WorkSiteReceptionStatusEnum::SCRATCH->value,
            'completion_status' => WorkSiteCompletionStatusEnum::PENDING->value,
            'contractor_id' => $contractor->id,
        ]);
        expect($workSite->parentWorksite)->toBeNull('that indicates that workSite is main')
            ->and($workSite?->title)->toBe('workSite A')
            ->and($workSite?->description)->toBe('this workSite is for freeTown')
            ->and($workSite?->items[0]->pivot->getAttributes())->toBe(
                ['work_site_id' => $workSite->id,
                    'item_id' => $workSiteResource1->id,
                    'quantity' => 23,
                    'price' => '34.00'])
            ->and($workSite?->items[1]->pivot->getAttributes())->toBe(
                ['work_site_id' => $workSite->id,
                    'item_id' => $workSiteResource2->id,
                    'quantity' => 30,
                    'price' => '30.00'])
            ->and($workSite->lastPayment->payable_id)->toBe($workSite->id)
            ->and($workSite->lastPayment->payable_type)->toBe('workSite')
            ->and($workSite->lastPayment->amount)->toBe('3000.00')
            ->and($workSite->lastPayment->payment_date)->toBe('2024-04-12 10:34:00')
            ->and($workSite->lastPayment->payment_type)->toBe(PaymentTypesEnum::CASH->value);

    });
    test('As a non-authenticated, I cant create a main workSite', function () {

        $response = $this->postJson('/api/v1/workSite/create', [
            'title' => 'workSite A',
            'description' => 'this workSite is for freeTown',
        ]);
        $response->assertStatus(Response::HTTP_UNAUTHORIZED);

    });
    test('As an administrator, should return validation error when no data', function () {

        $mainWorkSite = WorkSite::factory()->create();

        $admin = User::factory()->admin()->create();

        expect($admin->hasRole('admin'))->toBe(true);

        $response = $this->actingAs($admin)->postJson('/api/v1/workSite/create', [
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJson([
                'success' => false,
                'error_code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'message' => 'The title field is required.',
                'data' => [
                    'title' => ['The title field is required.'],
                    'description' => ['The description field is required.'],
                ],
            ]);

    });
    test('As not admin, I cant create a main workSite', function () {

        $siteManager = User::factory()->siteManager()->create();
        expect($siteManager->hasRole('site_manager'))->toBe(true);

        $response = $this->actingAs($siteManager)->postJson('/api/v1/workSite/create', [
            'title' => 'workSite A',
            'description' => 'this workSite is for freeTown',
        ]);
        $response->assertStatus(Response::HTTP_FORBIDDEN);

    });
    test('As an administrator, I want to create a sub worksites', function () {

        $mainWorkSite = WorkSite::factory()->create();
        $address = Address::factory()->create();

        $admin = User::factory()->admin()->create();

        expect($admin->hasRole('admin'))->toBe(true);

        $response = actingAs($admin)->postJson('/api/v1/workSite/create', [
            'title' => 'workSite AB',
            'description' => 'this workSite is for freeTown',
            'customer_id' => $mainWorkSite->customer?->id,
            'category_id' => $mainWorkSite->category?->id, // construction
            'parent_work_site_id' => $mainWorkSite->id, // this is main workSite == top level workSite
            'starting_budget' => 15,
            'cost' => 20,
            'address_id' => $address->id,
            'workers_count' => 20,
            'receipt_date' => '2024-04-12',
            'starting_date' => '2024-04-12',
            'deliver_date' => '2024-04-12',
            'reception_status' => 1,

        ]);
        $response->assertOk();

        $workSite = WorkSite::query()->latest('id')->first();

        expect($workSite?->title)->toBe('workSite AB')
            ->and($workSite?->description)->toBe('this workSite is for freeTown')
            ->and($workSite->parentWorksite->id)->toBe($mainWorkSite->id);

    });

});
describe('Update WorkSite', function () {

    beforeEach(function () {

        $this->admin = User::factory()->admin()->create();
        $this->workSite = WorkSite::factory()->create();

    });

    test('As a non-authenticated, I cant update a main workSite', function () {
        $response = putJson('/api/v1/workSite/update/'.$this->workSite->id, []);
        $response->assertStatus(Response::HTTP_UNAUTHORIZED);

    });
    test('As not admin, I cant update a main workSite', function () {

        $siteManager = User::factory()->siteManager()->create();
        expect($siteManager->hasRole('site_manager'))->toBe(true);

        $response = actingAs($siteManager)->putJson('/api/v1/workSite/update/'.$this->workSite->id, []);
        $response->assertStatus(Response::HTTP_FORBIDDEN);

    });
    test('As an administrator, I want to update workSite main info', function () {

        $address = Address::factory()->create();
        assertDatabaseCount(WorkSite::class, 1);
        $response = actingAs($this->admin)->putJson('/api/v1/workSite/update/'.$this->workSite->id, [
            'address_id' => $address->id,
            'title' => 'workSite AB',
            'description' => 'this workSite is for freeTown new',
        ]);
        $response->assertStatus(Response::HTTP_OK);
        assertDatabaseHas(WorkSite::class, [
            'address_id' => $address->id,
            'title' => 'workSite AB',
            'description' => 'this workSite is for freeTown new',
        ]);

    });
    test('As an administrator, I want to update workSite contractor before workSite finished', function () {

    });
});
describe('List WorkSites', function () {

    beforeEach(function () {

        $this->admin = User::factory()->admin()->create();
        $this->notAdmin = User::factory()->siteManager()->create();
        expect($this->notAdmin->hasRole('site_manager'))->toBe(true);

    });
    test('As a non-authenticated, I cant show list of worksites', function () {
        $response = getJson('/api/v1/workSite/list');
        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    });
    test('As not admin, I cant show list of worksites', function () {
        $response = actingAs($this->notAdmin)->getJson('/api/v1/workSite/list');
        $response->assertStatus(Response::HTTP_FORBIDDEN);
    });
    test('As an admin, I can show list of worksites without customer and category while creating', function () {
        $address = Address::factory()->create();
        $data = [
            'title' => 'workSite A',
            'description' => 'this workSite is for freeTown',
            'customer_id' => null,
            'category_id' => null, // construction
            'parent_work_site_id' => null, // this is main workSite == top level workSite
            'starting_budget' => 15,
            'cost' => 20,
            'address_id' => $address->id,
            'workers_count' => 20,
            'receipt_date' => '2024-04-12',
            'starting_date' => '2024-04-12',
            'deliver_date' => '2024-04-12',
            'reception_status' => WorkSiteReceptionStatusEnum::SCRATCH->value,
            'created_at' => now(),
            'updated_at' => now(),
        ];
        $workSite = WorkSite::factory()->create($data);
        assertDatabaseCount(WorkSite::class, 1);
        actingAs($this->admin)->getJson('/api/v1/workSite/list')
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonFragment([
                'success' => true,
                'error_code' => null,
            ])
            ->assertJsonFragment([
                'id' => $workSite->id,
                'title' => $workSite->title,
                'description' => $workSite->description,
                'customer' => $workSite->customer?->fullName,
                'category' => $workSite->category?->name,
                'sub_worksites' => [],
                'starting_budget' => number_format($workSite->starting_budget, 2),
                'cost' => number_format($workSite->cost, 2),
                'address' => [
                    'id' => $address->id,
                    'title' => $address->title,
                    'city' => $address->city?->name,
                    'street' => $address->street,
                    'state' => $address->state,
                    'zipCode' => $address->zipcode,
                ],
                'workers_count' => $workSite->workers_count,
                'receipt_date' => $workSite->receipt_date,
                'starting_date' => $workSite->starting_date,
                'deliver_date' => $workSite->deliver_date,
                'reception_status' => $workSite->reception_status,
                'created_at' => Carbon::parse($workSite->created_at)->toDateTimeString(),
                'updated_at' => Carbon::parse($workSite->updated_at)->toDateTimeString(),
                'payments' => $workSite->payments,
            ]);

    });
    test('As an admin, I can show list of worksites', function () {
        $wsCategory = WorkSiteCategory::factory()->create();
        $customer = Customer::factory()->create();
        $address = Address::factory()->create();
        $data = [
            'title' => 'workSite A',
            'description' => 'this workSite is for freeTown',
            'customer_id' => $customer?->id,
            'category_id' => $wsCategory?->id, // construction
            'parent_work_site_id' => null, // this is main workSite == top level workSite
            'starting_budget' => 15,
            'cost' => 20,
            'address_id' => $address->id,
            'workers_count' => 20,
            'receipt_date' => '2024-04-12',
            'starting_date' => '2024-04-12',
            'deliver_date' => '2024-04-12',
            'reception_status' => WorkSiteReceptionStatusEnum::SCRATCH->value,
            'created_at' => now(),
            'updated_at' => now(),
        ];
        $workSite = WorkSite::factory()->create($data);
        assertDatabaseCount(WorkSite::class, 1);
        actingAs($this->admin)->getJson('/api/v1/workSite/list')
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonFragment([
                'id' => $workSite->id,
                'title' => $workSite->title,
                'description' => $workSite->description,
                'customer' => $workSite->customer->fullName,
                'category' => $workSite->category->name,
                'sub_worksites' => $workSite->subWorkSites,
                'starting_budget' => number_format($workSite->starting_budget, 2),
                'cost' => number_format($workSite->cost, 2),
                'address' => [
                    'id' => $address->id,
                    'city' => $address->city?->name,
                    'title' => $address->title,
                    'street' => $address->street,
                    'state' => $address->state,
                    'zipCode' => $address->zipcode,
                ],
                'workers_count' => $workSite->workers_count,
                'receipt_date' => $workSite->receipt_date,
                'starting_date' => $workSite->starting_date,
                'deliver_date' => $workSite->deliver_date,
                'reception_status' => $workSite->reception_status,
                'created_at' => Carbon::parse($workSite->created_at)->toDateTimeString(),
                'updated_at' => Carbon::parse($workSite->updated_at)->toDateTimeString(),
                'payments' => $workSite->payments,
            ]);

    });

});
describe('Show WorkSites Details', function () {

    beforeEach(function () {

        $this->admin = User::factory()->admin()->create();
        $this->notAdmin = User::factory()->siteManager()->create();
        expect($this->notAdmin->hasRole('site_manager'))->toBe(true);

        $this->workSite = WorkSite::factory()->create();
        $this->wsCategory = WorkSiteCategory::factory()->create();
        $this->customer = Customer::factory()->create();
        $this->address = Address::factory()->create();
        $data = [
            'title' => 'workSite sub',
            'description' => 'this workSite is for freeTown sub',
            'customer_id' => $this->customer?->id,
            'category_id' => $this->wsCategory?->id, // construction
            'parent_work_site_id' => $this->workSite->id, // this is main workSite == top level workSite
            'starting_budget' => 15,
            'cost' => 20,
            'address_id' => $this->address->id,
            'workers_count' => 20,
            'receipt_date' => '2024-04-12',
            'starting_date' => '2024-04-12',
            'deliver_date' => '2024-04-12',
            'reception_status' => WorkSiteReceptionStatusEnum::SCRATCH->value,
            'created_at' => now(),
            'updated_at' => now(),
        ];
        $this->subWorkSite = WorkSite::factory()->create($data);
    });
    test('As a non-authenticated, I cant show details of a workSite', function () {
        $response = getJson('/api/v1/workSite/show/'.$this->workSite->id);
        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    });
    test('As not admin, I cant show details of a workSite', function () {
        $response = actingAs($this->notAdmin)->getJson('/api/v1/workSite/show/'.$this->workSite->id);
        $response->assertStatus(Response::HTTP_FORBIDDEN);
    });
    it('should return not found error if workSite not existed in database', function () {
        $unExistedWorkSiteId = rand(200, 333);
        $response = actingAs($this->admin)->getJson('/api/v1/workSite/show/'.$unExistedWorkSiteId);
        $response->assertStatus(Response::HTTP_NOT_FOUND);
    });
    test('As an admin, I can show details of a workSite', function () {
        $response = actingAs($this->admin)->getJson('/api/v1/workSite/show/'.$this->workSite->id);
        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonFragment([
                'id' => $this->workSite->id,
                'title' => $this->workSite->title,
                'description' => $this->workSite->description,
                'customer' => $this->workSite->customer?->fullName,
                'category' => $this->workSite->category?->name, // construction
                'starting_budget' => number_format($this->workSite->starting_budget, 2),
                'cost' => number_format($this->workSite->cost, 2),
                'address' => [
                    'id' => $this->workSite->address->id,
                    'title' => $this->workSite->address->title,
                    'city' => $this->workSite->address->city?->name,
                    'street' => $this->workSite->address->street,
                    'state' => $this->workSite->address->state,
                    'zipCode' => $this->workSite->address->zipcode,
                ],
                'workers_count' => $this->workSite->workers_count,
                'receipt_date' => $this->workSite->receipt_date,
                'starting_date' => $this->workSite->starting_date,
                'deliver_date' => $this->workSite->deliver_date,
                'reception_status' => $this->workSite->reception_status,
                'created_at' => Carbon::parse($this->workSite->created_at)->toDateTimeString(),
                'updated_at' => Carbon::parse($this->workSite->updated_at)->toDateTimeString(),
            ])
            ->assertJsonPath('data.sub_worksites', [
                [
                    'id' => $this->subWorkSite->id,
                    'title' => $this->subWorkSite->title,
                    'description' => $this->subWorkSite->description,
                    'customer' => $this->subWorkSite->customer?->fullName,
                    'category' => $this->subWorkSite->category?->name,
                    'sub_worksites' => [],
                    'starting_budget' => number_format($this->subWorkSite->starting_budget, 2),
                    'cost' => number_format($this->subWorkSite->cost, 2),
                    'address' => [
                        'id' => $this->address->id,
                        'title' => $this->address->title,
                        'city' => $this->address->city?->name,
                        'street' => $this->address->street,
                        'state' => $this->address->state,
                        'zipCode' => $this->address->zipcode,
                    ],
                    'workers_count' => $this->subWorkSite->workers_count,
                    'receipt_date' => $this->subWorkSite->receipt_date,
                    'starting_date' => $this->subWorkSite->starting_date,
                    'deliver_date' => $this->subWorkSite->deliver_date,
                    'reception_status' => $this->subWorkSite->reception_status,
                    'created_at' => Carbon::parse($this->subWorkSite->created_at)->toDateTimeString(),
                    'updated_at' => Carbon::parse($this->subWorkSite->updated_at)->toDateTimeString(),
                    'payments' => [],
                    'items' => [],
                ],
            ]);
    });
    test('As an admin, I can show details of a workSite with payments and items', function () {

        $workSiteResourceCategory = ItemCategory::factory()->create();

        $workSiteResource1 = Item::factory()->create([
            'item_category_id' => $workSiteResourceCategory->id,
        ]);
        $workSiteResource2 = Item::factory()->create([
            'item_category_id' => $workSiteResourceCategory->id,
        ]);
        $data = [
            'title' => 'workSite sub',
            'description' => 'this workSite is for freeTown sub',
            'customer_id' => $this->customer?->id,
            'category_id' => $this->wsCategory?->id, // construction
            'parent_work_site_id' => $this->workSite->id, // this is main workSite == top level workSite
            'starting_budget' => 15,
            'cost' => 20,
            'address_id' => $this->address->id,
            'workers_count' => 20,
            'receipt_date' => '2024-04-12',
            'starting_date' => '2024-04-12',
            'deliver_date' => '2024-04-12',
            'reception_status' => WorkSiteReceptionStatusEnum::SCRATCH->value,
            'created_at' => now(),
            'updated_at' => now(),
        ];
        $workSite = WorkSite::factory()->create($data);

        $payment = \App\Models\Payment::factory()->create([
            'payable_id' => $workSite->id,
            'payable_type' => 'workSite',
            'amount' => 20,
            'payment_date' => Carbon::now(),
            'payment_type' => PaymentTypesEnum::CASH->value,
        ]);

        $workSite->items()->syncWithoutDetaching([
            $workSiteResource1->id => [
                'quantity' => 23,
                'price' => '34.00',
            ],
        ]);

        $response = actingAs($this->admin)->getJson('/api/v1/workSite/show/'.$workSite->id);
        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonFragment([
                'id' => $workSite->id,
                'title' => $workSite->title,
                'description' => $workSite->description,
                'customer' => $workSite->customer?->fullName,
                'category' => $workSite->category?->name, // construction
                'starting_budget' => number_format($workSite->starting_budget, 2),
                'cost' => number_format($workSite->cost, 2),
                'address' => [
                    'id' => $workSite->address->id,
                    'title' => $this->address->title,
                    'city' => $workSite->address->city?->name,
                    'street' => $workSite->address->street,
                    'state' => $workSite->address->state,
                    'zipCode' => $workSite->address->zipcode,
                ],
                'workers_count' => $workSite->workers_count,
                'receipt_date' => $workSite->receipt_date,
                'starting_date' => $workSite->starting_date,
                'deliver_date' => $workSite->deliver_date,
                'reception_status' => $workSite->reception_status,
                'created_at' => Carbon::parse($workSite->created_at)->toDateTimeString(),
                'updated_at' => Carbon::parse($workSite->updated_at)->toDateTimeString(),
                'payments' => [
                    [
                        'id' => $payment->id,
                        'payable_id' => $workSite->id,
                        'payable_type' => 'workSite',
                        'amount' => number_format(20, 2),
                        'date' => Carbon::parse(Carbon::now())->toDateTimeString(),
                        'payment_type' => PaymentTypesEnum::CASH->value,
                    ],
                ],
                'items' => [
                    [
                        'name' => $workSiteResource1->name,
                        'description' => $workSiteResource1->description,
                        'item_category' => [
                            'id' => $workSiteResourceCategory->id,
                            'name' => $workSiteResourceCategory->name,
                        ],
                        'work_site_id' => $workSite->id,
                        'item_id' => $workSiteResource1->id,
                        'quantity' => 23,
                        'price' => '34.00',
                    ],
                ],
            ]);
    });

});
describe('Close WorkSites', function () {

    beforeEach(function () {

        $this->admin = User::factory()->admin()->create();
        $this->notAdmin = User::factory()->siteManager()->create();
        expect($this->notAdmin->hasRole('site_manager'))->toBe(true);

        $this->workSite = WorkSite::factory()->create([
            'cost' => 2000,
        ]);

    });
    test('As a non-authenticated, I cant close a workSite', function () {
        $response = postJson('/api/v1/workSite/close/'.$this->workSite->id);
        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    });
    test('As not admin, I cant close a workSite', function () {
        $response = actingAs($this->notAdmin)->postJson('/api/v1/workSite/close/'.$this->workSite->id);
        $response->assertStatus(Response::HTTP_FORBIDDEN);
    });
    it('should return not found error if workSite not existed in database', function () {
        $unExistedWorkSiteId = rand(200, 333);
        $response = actingAs($this->admin)->postJson('/api/v1/workSite/close/'.$unExistedWorkSiteId);
        $response->assertStatus(Response::HTTP_NOT_FOUND);
    });
    it('should prevent me closing workSite with active worksites', function () {
        WorkSite::factory()->create([
            'completion_status' => WorkSiteCompletionStatusEnum::STARTED,
            'parent_work_site_id' => $this->workSite->id,
        ]);
        $response = actingAs($this->admin)->postJson('/api/v1/workSite/close/'.$this->workSite->id);
        $response->assertStatus(Response::HTTP_CONFLICT)
            ->assertJson([
                'message' => "You can't close a workSite with active sub-worksites",
            ]);

    });
    it('should prevent me closing workSite with unpaid payments', function () {
        \App\Models\Payment::factory()->create([
            'payable_id' => $this->workSite->id,
            'payable_type' => 'workSite',
            'amount' => 1000,
            'payment_date' => Carbon::now(),
            'payment_type' => PaymentTypesEnum::CASH->value,
        ]);
        \App\Models\Payment::factory()->create([
            'payable_id' => $this->workSite->id,
            'payable_type' => 'workSite',
            'amount' => 100,
            'payment_date' => Carbon::now(),
            'payment_type' => PaymentTypesEnum::CASH->value,
        ]);
        $response = actingAs($this->admin)->postJson('/api/v1/workSite/close/'.$this->workSite->id);

        $response->assertStatus(Response::HTTP_CONFLICT)
            ->assertJson([
                'message' => "You can't close a workSite with unpaid payment",
            ]);
    });
    test('As an admin, I can close a workSite with full payments and closed sub worksites', function () {
        \App\Models\Payment::factory()->create([
            'payable_id' => $this->workSite->id,
            'payable_type' => 'workSite',
            'amount' => $this->workSite->cost,
            'payment_date' => Carbon::now(),
            'payment_type' => PaymentTypesEnum::CASH->value,
        ]);
        WorkSite::factory()->create([
            'completion_status' => WorkSiteCompletionStatusEnum::CLOSED,
            'parent_work_site_id' => $this->workSite->id,
        ]);
        $response = actingAs($this->admin)->postJson('/api/v1/workSite/close/'.$this->workSite->id);
        $response->assertStatus(Response::HTTP_OK);

        assertDatabaseHas(WorkSite::class, [
            'completion_status' => WorkSiteCompletionStatusEnum::CLOSED,
        ]);
    });

});
describe('Assign Contractor to WorkSites', function () {

    beforeEach(function () {

        $this->admin = User::factory()->admin()->create();
        $this->notAdmin = User::factory()->siteManager()->create();
        expect($this->notAdmin->hasRole('site_manager'))->toBe(true);

        $this->workSite = WorkSite::factory()->create();
        $this->contractor = Contractor::factory()->create();

    });
    test('As a non-authenticated, I cant assign contractor to a workSite', function () {
        $response = putJson('/api/v1/workSite/'.$this->workSite->id.'/contractor/'.$this->contractor->id.'/assign');
        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    });
    test('As not admin, I cant assign contractor to a workSite', function () {
        $response = actingAs($this->notAdmin)->putJson('/api/v1/workSite/'.$this->workSite->id.'/contractor/'.$this->contractor->id.'/assign');
        $response->assertStatus(Response::HTTP_FORBIDDEN);
    });
    it('should return not found error if workSite not existed in database and if contractor not existed', function () {
        $unExistedWorkSiteId = rand(200, 333);
        $unExistedContractorId = rand(200, 333);
        $response = actingAs($this->admin)->putJson('/api/v1/workSite/'.$unExistedWorkSiteId.'/contractor/'.$this->contractor->id.'/assign');
        $response->assertStatus(Response::HTTP_NOT_FOUND);

        $otherResponse = actingAs($this->admin)->putJson('/api/v1/workSite/'.$this->workSite->id.'/contractor/'.$unExistedContractorId.'/assign');
        $otherResponse->assertStatus(Response::HTTP_NOT_FOUND);
    });
    it('should add contractor of a workSite', function () {
        $response = actingAs($this->admin)->putJson('/api/v1/workSite/'.$this->workSite->id.'/contractor/'.$this->contractor->id.'/assign');
        $response->assertStatus(Response::HTTP_OK);
        assertDatabaseHas(WorkSite::class, [
            'contractor_id' => $this->contractor->id,
        ]);

    });
    it('should update contractor of a workSite', function () {
        $response = actingAs($this->admin)->putJson('/api/v1/workSite/'.$this->workSite->id.'/contractor/'.$this->contractor->id.'/assign');
        $response->assertStatus(Response::HTTP_OK);
        assertDatabaseHas(WorkSite::class, [
            'contractor_id' => $this->contractor->id,
        ]);

        $otherContractor = Contractor::factory()->create();

        $response = actingAs($this->admin)->putJson('/api/v1/workSite/'.$this->workSite->id.'/contractor/'.$otherContractor->id.'/assign');
        $response->assertStatus(Response::HTTP_OK);
        assertDatabaseHas(WorkSite::class, [
            'contractor_id' => $otherContractor->id,
        ]);

    });
    test('As an admin i can remove contractor of a workSite ', function () {
        $response = actingAs($this->admin)->putJson('/api/v1/workSite/'.$this->workSite->id.'/contractor/'.$this->contractor->id.'/assign');
        $response->assertStatus(Response::HTTP_OK);
        assertDatabaseHas(WorkSite::class, [
            'contractor_id' => $this->contractor->id,
        ]);

        $response = actingAs($this->admin)->putJson('/api/v1/workSite/'.$this->workSite->id.'/contractor/'.$this->contractor->id.'/unAssign');
        $response->assertStatus(Response::HTTP_OK);
        assertDatabaseHas(WorkSite::class, [
            'contractor_id' => null,
        ]);

    });
});
describe('Manage items of the workSite', function () {
    test('As a non-authenticated, I cant manage resource of a workSite', function () {

    });
    test('As not admin, I cant manage resource of a workSite', function () {

    });

    it('should add new resource to a workSite', function () {

    });
    it('should update existed resource of a workSite', function () {

    });
    it('should delete items of a workSite', function () {

    });
    it('should show all items a workSite', function () {

    });
    it('should show details of a resource in the workSite', function () {

    });
});
describe('Manage employees of the workSite', function () {
    test('As a non-authenticated, I cant manage workers of a workSite', function () {

    });
    test('As not admin, I cant manage workers of a workSite', function () {

    });
    it('should add new worker to a workSite', function () {

    });
    it('should update a worker of a workSite', function () {

    });
    it('should delete a worker of a workSite', function () {

    });
    it('should show all workers of the workSite', function () {

    });
    it('should show details of a worker in a workSite', function () {

    });
});
describe('manage customer of the workSite', function () {
    test('As a non-authenticated, I cant manage resource of a workSite', function () {

    });
    test('As not admin, I cant manage resource of a workSite', function () {

    });

    it('should add new customer to a workSite', function () {

    });
    it('should update a customer details of a workSite', function () {

    });
    it('should prevent delete a customer of an active workSite', function () {

    });
    it('should delete a customer of a closed workSite', function () {

    });
    it('should show details of a customer in a workSite', function () {

    });
});

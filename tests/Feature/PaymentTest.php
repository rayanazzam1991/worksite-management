<?php

use App\Enums\PaymentTypesEnum;
use App\Models\Payment;
use App\Models\User;
use App\Models\WorkSite;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\Response;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;

describe('Create Payment for a Worksite', function () {

    beforeEach(function () {

        $this->workSite = WorkSite::factory()->create();

        $this->admin = User::factory()->admin()->create();
        $this->notAdmin = User::factory()->worker()->create();

        expect($this->admin->hasRole('admin'))->toBe(true);
    });

    it('should prevent non auth making new payment for a workSite', function () {
        $response = postJson('/api/v1/workSite/'.$this->workSite->id.'/payment/create');
        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    });
    it('should prevent non admin making new payment for a workSite', function () {

        $response = actingAs($this->notAdmin)->postJson('/api/v1/workSite/'.$this->workSite->id.'/payment/create');
        $response->assertStatus(Response::HTTP_FORBIDDEN);
    });
    test('As an administrator, I want to make payment to workSite', function () {

        $response = actingAs($this->admin)->postJson('/api/v1/workSite/'.$this->workSite->id.'/payment/create', [
            'amount' => 3000,
            'payment_type' => PaymentTypesEnum::CASH->value,
            'payment_date' => '2024-04-12 10:34',
            'payable_id' => $this->workSite->id,
            'payable_type' => WorkSite::class,

        ]);
        $expectedResult = [
            'amount' => '3000.00',
            'payment_type' => PaymentTypesEnum::CASH->value,
            'payment_date' => '2024-04-12 10:34:00',
            'payable_id' => $this->workSite->id,
            'payable_type' => 'workSite',
        ];
        $response->assertOk();
        assertDatabaseHas(Payment::class, $expectedResult);

        expect($expectedResult)->toBe([
            'amount' => $this->workSite->lastPayment->amount,
            'payment_type' => $this->workSite->lastPayment->payment_type,
            'payment_date' => $this->workSite->lastPayment->payment_date,
            'payable_id' => $this->workSite->lastPayment->payable_id,
            'payable_type' => $this->workSite->lastPayment->payable_type,
        ]);

    });

});
describe('List Payments for a Worksite', function () {

    beforeEach(function () {

        $this->workSite = WorkSite::factory()->create();

        $this->firstPayment = Payment::factory()->create([
            'amount' => 3000,
            'payment_type' => PaymentTypesEnum::CASH->value,
            'payment_date' => '2024-04-12 10:34:00',
            'payable_id' => $this->workSite->id,
            'payable_type' => WorkSite::class,

        ]);

        $this->secondPayment = Payment::factory()->create([
            'amount' => 2000,
            'payment_type' => PaymentTypesEnum::CASH->value,
            'payment_date' => '2024-04-20 10:34:00',
            'payable_id' => $this->workSite->id,
            'payable_type' => WorkSite::class,

        ]);

        $this->admin = User::factory()->admin()->create();
        $this->notAdmin = User::factory()->worker()->create();

        expect($this->admin->hasRole('admin'))->toBe(true);
    });

    it('should prevent non auth show all payments of a workSite', function () {
        $response = getJson('/api/v1/workSite/'.$this->workSite->id.'/payment/list');
        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    });
    it('should prevent non admin show all payments of a workSite', function () {

        $response = actingAs($this->notAdmin)->getJson('/api/v1/workSite/'.$this->workSite->id.'/payment/list');
        $response->assertStatus(Response::HTTP_FORBIDDEN);
    });
    test('As an administrator, I want to show all payments of a workSite', function () {

        $query = '?date_from=2024-04-11 10:34:00&date_to=2024-04-22 10:34:00';
        $response = actingAs($this->admin)
            ->getJson('/api/v1/workSite/'.$this->workSite->id.'/payment/list'.$query);
        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'amount',
                        'payment_type',
                        'payment_date',
                    ],
                ],
            ])
            ->assertJsonFragment([
                'amount' => '3000.00',
                'payment_type' => PaymentTypesEnum::CASH->name,
                'payment_date' => Carbon::parse($this->firstPayment->payment_date)->format('Y-m-d H:i:s'),
            ])
            ->assertJsonFragment([
                'amount' => '2000.00',
                'payment_type' => PaymentTypesEnum::CASH->name,
                'payment_date' => Carbon::parse($this->secondPayment->payment_date)->format('Y-m-d H:i:s'),
            ]);

    });
    test('As an administrator, I want to show payments in date range of a workSite', function () {

        $query = '?date_from=2024-04-11 10:34:00&date_to=2024-04-18 10:34:00';
        $response = actingAs($this->admin)
            ->getJson('/api/v1/workSite/'.$this->workSite->id.'/payment/list'.$query);
        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'amount',
                        'payment_type',
                        'payment_date',
                    ],
                ],
            ])
            ->assertJsonFragment([
                'amount' => '3000.00',
                'payment_type' => PaymentTypesEnum::CASH->name,
                'payment_date' => Carbon::parse($this->firstPayment->payment_date)->format('Y-m-d H:i:s'),
            ])
            ->assertJsonMissingExact([
                'amount' => '2000.00',
                'payment_type' => PaymentTypesEnum::CASH->name,
                'payment_date' => Carbon::parse($this->secondPayment->payment_date)->format('Y-m-d H:i:s'),
            ]);

    });
});

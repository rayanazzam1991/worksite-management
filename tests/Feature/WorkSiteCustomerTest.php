<?php

use App\Models\Customer;
use App\Models\User;
use App\Models\WorkSite;
use Symfony\Component\HttpFoundation\Response;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\postJson;

describe('WorkSite Customer assign', function () {

    beforeEach(function () {

        $this->notAdmin = User::factory()->worker()->create(['email' => 'not_admin@admin.com']);
        $this->admin = User::factory()->admin()->create(['email' => 'admin@admin.com']);

        $this->workSite = Worksite::factory()->create();
        $this->customer = Customer::factory()->create();
    });

    it('should prevent non auth assigning a Customer to workSite', function () {
        $response = postJson('/api/v1/workSite/'.$this->workSite->id.'/customer/'.$this->customer->id.'/assign');
        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    });
    it('should prevent non admin assigning a Customer to workSite', function () {

        $response = actingAs($this->notAdmin)
            ->postJson('/api/v1/workSite/'.$this->workSite->id.'/customer/'.$this->customer->id.'/assign');
        $response->assertStatus(Response::HTTP_FORBIDDEN);
    });
    it('should return not found error when workSite not found', function () {
        $undefinedWorkSiteId = rand(222, 333);
        $response = actingAs($this->admin)
            ->postJson('/api/v1/workSite/'.$undefinedWorkSiteId.'/customer/'.$this->customer->id.'/assign');
        $response->assertStatus(Response::HTTP_NOT_FOUND);
    });
    it('should return not found error when customer not found', function () {
        $undefinedCustomerId = rand(222, 333);
        $response = actingAs($this->admin)
            ->postJson('/api/v1/workSite/'.$this->workSite->id.'/customer/'.$undefinedCustomerId.'/assign');
        $response->assertStatus(Response::HTTP_NOT_FOUND);
    });
    it('should assign valid customer to a valid workSite', function () {
        $response = actingAs($this->admin)
            ->postJson('/api/v1/workSite/'.$this->workSite->id.'/customer/'.$this->customer->id.'/assign');
        $response->assertOk();
        assertDatabaseHas(WorkSite::class, [
            'customer_id' => $this->customer->id,
        ]);
    });
});

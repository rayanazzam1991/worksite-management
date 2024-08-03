<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse\ApiResponseHelper;
use App\Models\Customer;
use App\Models\WorkSite;
use Illuminate\Http\JsonResponse;

class WorkSiteCustomerController extends Controller
{
    public function assignCustomer(int $workSiteId, int $customerId): JsonResponse
    {

        $workSite = WorkSite::query()->findOrFail($workSiteId);
        $customer = Customer::query()->findOrFail($customerId);

        $workSite->update([
            'customer_id' => $customer->id,
        ]);

        return ApiResponseHelper::sendSuccessResponse();
    }
}

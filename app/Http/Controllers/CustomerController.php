<?php

namespace App\Http\Controllers;

use App\Enums\WorkSiteCompletionStatusEnum;
use App\Exceptions\UnAbleToDeleteCustomerException;
use App\Helpers\ApiResponse\ApiResponseHelper;
use App\Helpers\ApiResponse\Result;
use App\Http\Requests\CustomerCreateRequest;
use App\Http\Requests\CustomerUpdateRequest;
use App\Http\Resources\CustomerDetailsResource;
use App\Http\Resources\CustomerListResource;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;

class CustomerController extends Controller
{
    public function list(): JsonResponse
    {
        $customers = Customer::query()->get();

        return ApiResponseHelper::sendSuccessResponse(new Result(CustomerListResource::collection($customers)));
    }

    public function store(CustomerCreateRequest $request): JsonResponse
    {
        Customer::query()->create($request->validated());

        return ApiResponseHelper::sendSuccessResponse();

    }

    public function show(int $id): JsonResponse
    {
        $customer = Customer::query()->with(['address', 'payments'])->findOrFail($id);

        return ApiResponseHelper::sendSuccessResponse(new Result(CustomerDetailsResource::make($customer)));
    }

    public function update(CustomerUpdateRequest $request, int $id): void
    {
        $customer = Customer::query()->findOrFail($id);
        $customer->update($request->validated());
    }

    /**
     * @throws UnAbleToDeleteCustomerException
     */
    public function destroy(int $id): void
    {

        $customer = Customer::query()->findOrFail($id);

        $relatedWorkSite = $customer->whereHas('workSite', function ($query) {
            $query->where('completion_status', '<>', WorkSiteCompletionStatusEnum::CLOSED);
        })->exists();
        if ($relatedWorkSite) {
            throw new UnAbleToDeleteCustomerException('Unable to delete customer with a not closed work site');
        }
        $customer->delete();

    }
}

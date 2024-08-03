<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse\ApiResponseHelper;
use App\Helpers\ApiResponse\Result;
use App\Http\Requests\ContractorCreateRequest;
use App\Http\Requests\ContractorUpdateRequest;
use App\Http\Resources\ContractorDetailsResource;
use App\Http\Resources\ContractorListResource;
use App\Models\Contractor;
use Illuminate\Http\JsonResponse;
use Throwable;

class ContractorController extends Controller
{
    public function list(): JsonResponse
    {
        $result = Contractor::query()->get();

        return ApiResponseHelper::sendSuccessResponse(new Result(ContractorListResource::collection($result)));
    }

    /**
     * @throws Throwable
     */
    public function store(ContractorCreateRequest $request): JsonResponse
    {
        /** @var array{
         * first_name: string,
         * last_name: string|null,
         * phone: string|null,
         * address_id: int|null
         * } $requestData
         */
        $requestData = $request->validated();
        $filteredData = array_filter([
            'first_name' => $requestData['first_name'],
            'last_name' => $requestData['last_name'] ?? null,
            'phone' => $requestData['phone'] ?? null,
            'address_id' => $requestData['address_id'] ?? null,
        ], fn ($value) => $value != null);

        Contractor::query()->create($filteredData);

        return ApiResponseHelper::sendSuccessResponse();

    }

    /**
     * @throws Throwable
     */
    public function update(ContractorUpdateRequest $request, int $id): JsonResponse
    {
        /** @var array{
         * first_name: string|null,
         * last_name: string|null,
         * phone: string|null,
         * address_id: int|null
         * } $requestData
         */
        $requestData = $request->validated();

        $contractor = Contractor::query()->findOrFail($id);
        $filteredData = array_filter([
            'first_name' => $requestData['first_name'] ?? null,
            'last_name' => $requestData['last_name'] ?? null,
            'phone' => $requestData['phone'] ?? null,
            'address_id' => $requestData['address_id'] ?? null,
        ], fn ($value) => $value != null);

        $contractor->update($filteredData);

        return ApiResponseHelper::sendSuccessResponse();

    }

    public function show(int $id): JsonResponse
    {
        $contractor = Contractor::query()->findOrFail($id);

        return ApiResponseHelper::sendSuccessResponse(new Result(ContractorDetailsResource::make($contractor)));
    }
}

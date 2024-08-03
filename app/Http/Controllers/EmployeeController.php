<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse\ApiResponseHelper;
use App\Helpers\ApiResponse\Result;
use App\Http\Requests\EmployeeCreateRequest;
use App\Http\Requests\EmployeeUpdateRequest;
use App\Http\Resources\EmployeeDetailsResource;
use App\Http\Resources\EmployeeListResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Throwable;

class EmployeeController extends Controller
{
    public function list(): JsonResponse
    {
        $workers = User::query()->get();

        return ApiResponseHelper::sendSuccessResponse(new Result(EmployeeListResource::collection($workers)));
    }

    public function store(EmployeeCreateRequest $request): JsonResponse
    {
        /**
         * @var array{
         *     first_name:string,
         *     last_name:string|null,
         *     phone:string,
         *     password:string|null,
         * } $requestedData
         */
        $requestedData = $request->validated();
        $dataToSave = array_filter([
            'first_name' => $requestedData['first_name'],
            'last_name' => $requestedData['last_name'] ?? null,
            'phone' => $requestedData['phone'],
            'password' => $requestedData['password'] ?? '123456',
        ], fn ($value) => $value != null);

        User::query()->create($dataToSave);

        return ApiResponseHelper::sendSuccessResponse();
    }

    /**
     * @throws Throwable
     */
    public function update(EmployeeUpdateRequest $request, int $workerId): JsonResponse
    {
        /**
         * @var array{
         *     first_name:string|null,
         *     last_name:string|null,
         *     phone:string|null
         * } $requestedData
         */
        $requestedData = $request->validated();
        $worker = User::query()->findOrFail($workerId);

        $dataToSave = array_filter([
            'first_name' => $requestedData['first_name'] ?? null,
            'last_name' => $requestedData['last_name'] ?? null,
            'phone' => $requestedData['phone'] ?? null,
        ], fn ($value) => $value != null);

        $worker->update($dataToSave);

        return ApiResponseHelper::sendSuccessResponse();
    }

    public function show(int $workerId): JsonResponse
    {
        $worker = User::query()->findOrFail($workerId);

        return ApiResponseHelper::sendSuccessResponse(new Result(EmployeeDetailsResource::make($worker)));
    }

    public function destroy(int $workerId): JsonResponse
    {
        $worker = User::query()->findOrFail($workerId);
        $worker->delete();

        return ApiResponseHelper::sendSuccessResponse();
    }
}

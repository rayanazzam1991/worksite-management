<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse\ApiResponseHelper;
use App\Helpers\ApiResponse\Result;
use App\Http\Requests\WorkSiteCategoryCreateRequest;
use App\Http\Requests\WorkSiteCategoryUpdateRequest;
use App\Http\Resources\WorkSiteCategoryDetailsResource;
use App\Http\Resources\WorkSiteCategoryListResource;
use App\Models\WorkSiteCategory;
use Illuminate\Http\JsonResponse;

class WorkSiteCategoryController extends Controller
{
    public function list(): JsonResponse
    {
        $categories = WorkSiteCategory::query()->get();

        return ApiResponseHelper::sendSuccessResponse(new Result(WorkSiteCategoryListResource::collection($categories)));
    }

    public function store(WorkSiteCategoryCreateRequest $request): JsonResponse
    {
        WorkSiteCategory::query()->create($request->validated());

        return ApiResponseHelper::sendSuccessResponse();
    }

    public function show(int $id): JsonResponse
    {
        $category = WorkSiteCategory::query()->findOrFail($id);

        return ApiResponseHelper::sendSuccessResponse(new Result(WorkSiteCategoryDetailsResource::make($category)));
    }

    public function update(WorkSiteCategoryUpdateRequest $request, int $id): JsonResponse
    {

        $workSiteCategory = WorkSiteCategory::query()->findOrFail($id);
        $workSiteCategory->update($request->validated());

        return ApiResponseHelper::sendSuccessResponse();

    }

    public function destroy(int $id): JsonResponse
    {
        $workSiteCategory = WorkSiteCategory::query()->findOrFail($id);
        $workSiteCategory->delete();

        return ApiResponseHelper::sendSuccessResponse();
    }
}

<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse\ApiResponseHelper;
use App\Helpers\ApiResponse\Result;
use App\Http\Requests\ItemCategoryCreateRequest;
use App\Http\Requests\ItemCategoryUpdateRequest;
use App\Http\Resources\ItemCategoryDetailsResource;
use App\Http\Resources\ItemCategoryListResource;
use App\Models\Item;
use App\Models\ItemCategory;
use Illuminate\Http\JsonResponse;

class ItemCategoryController extends Controller
{
    public function list(): JsonResponse
    {
        $resourceCategories = ItemCategory::get();

        return ApiResponseHelper::sendSuccessResponse(new Result(ItemCategoryListResource::collection($resourceCategories)));
    }

    public function store(ItemCategoryCreateRequest $request): JsonResponse
    {
        ItemCategory::query()->create($request->validated());

        return ApiResponseHelper::sendSuccessResponse();
    }

    public function show(int $resourceId, int $resourceCategoryId): JsonResponse
    {
        Item::query()->findOrFail($resourceId);
        $resourceCategory = ItemCategory::query()->findOrFail($resourceCategoryId);

        return ApiResponseHelper::sendSuccessResponse(new Result(ItemCategoryDetailsResource::make($resourceCategory)));

    }

    public function update(ItemCategoryUpdateRequest $request, int $resourceId, int $resourceCategoryId): JsonResponse
    {

        Item::query()->findOrFail($resourceId);
        $resourceCategory = ItemCategory::query()->findOrFail($resourceCategoryId);
        $resourceCategory->update($request->validated());

        return ApiResponseHelper::sendSuccessResponse();

    }

    public function destroy(int $resourceId, int $resourceCategoryId): JsonResponse
    {
        Item::query()->findOrFail($resourceId);
        $resourceCategory = ItemCategory::query()->findOrFail($resourceCategoryId);
        $resourceCategory->delete();

        return ApiResponseHelper::sendSuccessResponse();
    }
}

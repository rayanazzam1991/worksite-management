<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse\ApiResponseHelper;
use App\Helpers\ApiResponse\Result;
use App\Http\Requests\ItemCreateRequest;
use App\Http\Requests\ItemUpdateRequest;
use App\Http\Resources\ItemDetailsResource;
use App\Http\Resources\ItemListResource;
use App\Models\Item;
use Illuminate\Http\JsonResponse;

class ItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function list(): JsonResponse
    {
        $resources = Item::query()->get();

        return ApiResponseHelper::sendSuccessResponse(new Result(ItemListResource::collection($resources)));
    }

    public function store(ItemCreateRequest $request): JsonResponse
    {
        Item::query()->create($request->validated());

        return ApiResponseHelper::sendSuccessResponse();
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id): JsonResponse
    {
        $resource = Item::query()->findOrFail($id);

        return ApiResponseHelper::sendSuccessResponse(new Result(ItemDetailsResource::make($resource)));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ItemUpdateRequest $request, int $id): JsonResponse
    {
        $resource = Item::query()->findOrFail($id);
        $resource->update($request->validated());

        return ApiResponseHelper::sendSuccessResponse();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $resource = Item::query()->findOrFail($id);
        $resource->delete();

        return ApiResponseHelper::sendSuccessResponse();
    }
}

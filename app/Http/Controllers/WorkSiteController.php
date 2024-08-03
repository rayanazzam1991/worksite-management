<?php

namespace App\Http\Controllers;

use App\Enums\PaymentTypesEnum;
use App\Enums\WorkSiteCompletionStatusEnum;
use App\Exceptions\UnAbleToCloseWorkSiteException;
use App\Helpers\ApiResponse\ApiResponseHelper;
use App\Helpers\ApiResponse\Result;
use App\Http\Requests\WorkSiteCreateRequest;
use App\Http\Requests\WorkSiteUpdateRequest;
use App\Http\Resources\WorkSiteDetailsResource;
use App\Http\Resources\WorkSiteListResource;
use App\Models\Contractor;
use App\Models\WorkSite;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Intervention\Image\Drivers\Gd\Driver;
use Throwable;

class WorkSiteController extends Controller
{
    public function list(): JsonResponse
    {
        $workSites = WorkSite::query()->with(['payments', 'address'])->get();

        return ApiResponseHelper::sendSuccessResponse(new Result(WorkSiteListResource::collection($workSites)));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @throws Throwable
     */
    public function store(WorkSiteCreateRequest $request): JsonResponse
    {

        DB::transaction(
            callback: function () use ($request) {
                /**
                 * @var array{
                 *  title: string,
                 *  description: string,
                 *  customer_id?: int|null,
                 *  category_id?: int|null,
                 *  parent_work_site_id?: int|null,
                 *  contractor_id?: int|null,
                 *  starting_budget?: float|null,
                 *  cost?: float|null,
                 *  address_id?: int|null,
                 *  workers_count?: int|null,
                 *  receipt_date?: string|null,
                 *  starting_date?: string|null,
                 *  deliver_date?: string|null,
                 *  reception_status?: int|null,
                 *  completion_status?: int|null,
                 *  items?: array<int,array{
                 *       id:int,
                 *       quantity:int,
                 *       price:float
                 *   }>|null,
                 *  payments?: array<int,array{
                 *       payment_amount:float,
                 *       payment_date: string
                 *   }>|null,
                 *  images?: string|null
                 *  } $requestedData
                 */
                $requestedData = $request->validated();
                $dataToSave = array_filter([
                    'title' => $requestedData['title'],
                    'description' => $requestedData['description'],
                    'customer_id' => $requestedData['customer_id'] ?? null,
                    'category_id' => $requestedData['category_id'] ?? null,
                    'contractor_id' => $requestedData['contractor_id'] ?? null,
                    'parent_work_site_id' => $requestedData['parent_work_site_id'] ?? null,
                    'starting_budget' => $requestedData['starting_budget'] ?? null,
                    'cost' => $requestedData['cost'] ?? null,
                    'address_id' => $requestedData['address_id'] ?? null,
                    'workers_count' => $requestedData['workers_count'] ?? null,
                    'receipt_date' => $requestedData['receipt_date'] ?? null,
                    'starting_date' => $requestedData['starting_date'] ?? null,
                    'deliver_date' => $requestedData['deliver_date'] ?? null,
                    'reception_status' => $requestedData['reception_status'] ?? null,
                    'completion_status' => $requestedData['completion_status'] ?? null,
                ], fn ($value) => $value != null);

                $workSite = WorkSite::query()->create($dataToSave);

                $resourcesData = [];
                if (array_key_exists('items', $requestedData) &&
                    is_array($requestedData['items']) && count($requestedData['items']) > 0) {
                    foreach ($requestedData['items'] as $resource) {
                        if (is_array($resource)) {
                            $item = [
                                'quantity' => $resource['quantity'],
                                'price' => $resource['price'],
                            ];
                            $resourcesData[$resource['id']] = $item;
                        }
                    }
                }
                $workSite->items()->syncWithoutDetaching($resourcesData);
                $paymentData = [];
                if (array_key_exists('payments', $requestedData) &&
                    is_array($requestedData['payments']) && count($requestedData['payments']) > 0) {
                    foreach ($requestedData['payments'] as $payment) {
                        if (is_array($payment)) {
                            $item = [
                                'amount' => $payment['payment_amount'],
                                'payment_date' => Carbon::parse($payment['payment_date']),
                                'payment_type' => PaymentTypesEnum::CASH->value,
                            ];
                            $paymentData[] = $item;
                        }
                    }
                }

                foreach ($paymentData as $payment) {
                    $workSite->payments()->create($payment);
                }

                $files = $request->file('images');
                if (isset($files) && is_array($files)) {
                    foreach ($files as $file) {
                        $fileNameParts = explode('.', $file->getClientOriginalName());
                        $fileName = $fileNameParts[0];
                        $path = lcfirst('WorkSite');
                        $name = $fileName.'_'.now()->format('YmdH');

                        if (! File::exists(public_path('storage/'.$path))) {
                            File::makeDirectory(public_path('storage/'.$path));
                        }

                        $fullPath = public_path('storage/'.$path).'/'.$name.'.webp';

                        // create new manager instance with desired driver
                        $manager = new \Intervention\Image\ImageManager(new Driver());

                        // read image from filesystem
                        $image = $manager->read($file)->save($fullPath);
                    }
                }
                //        $this->fileManager->upload($files);
            },
            attempts: 3);

        return ApiResponseHelper::sendSuccessResponse(
            new Result());
    }

    /**
     * Show the specified resource.
     */
    public function show(int $id): JsonResponse
    {
        $worksite = WorkSite::query()->with(['customer', 'payments', 'items'])->findOrFail($id);

        return ApiResponseHelper::sendSuccessResponse(new Result(WorkSiteDetailsResource::make($worksite)));
    }

    /**
     * Update the specified resource in storage.
     *
     * @throws Throwable
     */
    public function update(WorkSiteUpdateRequest $request, int $id): JsonResponse
    {

        DB::transaction(
            callback: function () use ($request, $id) {
                /**
                 * @var array{
                 *  title: string |null,
                 *  description: string|null,
                 *  customer_id?: int|null,
                 *  category_id?: int|null,
                 *  parent_work_site_id?: int|null,
                 *  contractor_id?: float|null,
                 *  starting_budget?: float|null,
                 *  cost?: float|null,
                 *  address_id?: int|null,
                 *  workers_count?: int|null,
                 *  receipt_date?: string|null,
                 *  starting_date?: string|null,
                 *  deliver_date?: string|null,
                 *  reception_status?: int|null,
                 *  completion_status?: int|null,
                 *  items?: array{
                 *    id:int,
                 *    quantity:int,
                 *    price:float
                 *  }|null,
                 *  payments?: array{
                 *    payment_amount:float,
                 *    payment_date: string
                 *  }|null,
                 *  image?: string|null
                 *  } $requestedData
                 */
                $requestedData = $request->validated();
                $workSite = WorkSite::query()->findOrFail($id);

                $dataToSave = array_filter([
                    'title' => $requestedData['title'] ?? null,
                    'description' => $requestedData['description'] ?? null,
                    'customer_id' => $requestedData['customer_id'] ?? null,
                    'category_id' => $requestedData['category_id'] ?? null,
                    'contractor_id' => $requestedData['contractor_id'] ?? null,
                    'parent_work_site_id' => $requestedData['parent_work_site_id'] ?? null,
                    'starting_budget' => $requestedData['starting_budget'] ?? null,
                    'cost' => $requestedData['cost'] ?? null,
                    'address_id' => $requestedData['address_id'] ?? null,
                    'workers_count' => $requestedData['workers_count'] ?? null,
                    'receipt_date' => $requestedData['receipt_date'] ?? null,
                    'starting_date' => $requestedData['starting_date'] ?? null,
                    'deliver_date' => $requestedData['deliver_date'] ?? null,
                    'reception_status' => $requestedData['reception_status'] ?? null,
                    'completion_status' => $requestedData['completion_status'] ?? null,
                ], fn ($value) => $value != null);

                $workSite->update($dataToSave);

            },
            attempts: 3);

        return ApiResponseHelper::sendSuccessResponse(
            new Result());
    }

    /**
     * @throws UnAbleToCloseWorkSiteException
     */
    public function close(int $id): JsonResponse
    {
        $workSite = WorkSite::query()->with(['subWorksites'])->findOrFail($id);
        $relatedActiveSubWorkSitesCount = $workSite->whereHas('subWorksites', function (Builder $query) {
            return $query->where(
                column: 'completion_status',
                operator: '<>',
                value: WorkSiteCompletionStatusEnum::CLOSED
            );
        })->count();

        $workSitePayments = $workSite->payments->sum('amount');

        if ($relatedActiveSubWorkSitesCount > 0) {
            throw new UnAbleToCloseWorkSiteException("You can't close a workSite with active sub-worksites");
        }

        if ($workSitePayments < $workSite->cost) {
            throw new UnAbleToCloseWorkSiteException("You can't close a workSite with unpaid payment");
        }

        $workSite->update([
            'completion_status' => WorkSiteCompletionStatusEnum::CLOSED,
        ]);

        return ApiResponseHelper::sendSuccessResponse();
    }

    /**
     * @throws Throwable
     */
    public function assignContractor(int $workSiteId, int $contractorId): JsonResponse
    {
        $workSite = WorkSite::query()->findOrFail($workSiteId);
        Contractor::query()->findOrFail($contractorId);
        $workSite->update([
            'contractor_id' => $contractorId,
        ]);

        return ApiResponseHelper::sendSuccessResponse();
    }

    public function unAssignContractor(int $workSiteId, int $contractorId): JsonResponse
    {
        $workSite = WorkSite::query()->findOrFail($workSiteId);
        Contractor::query()->findOrFail($contractorId);

        $workSite->update([
            'contractor_id' => null,
        ]);

        return ApiResponseHelper::sendSuccessResponse();
    }
}

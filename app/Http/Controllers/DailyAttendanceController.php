<?php

namespace App\Http\Controllers;

use App\Exceptions\InvalidSubWorkSiteAttendanceException;
use App\Helpers\ApiResponse\ApiResponseHelper;
use App\Helpers\ApiResponse\Result;
use App\Http\Requests\DailyAttendanceCreateRequest;
use App\Http\Requests\DailyAttendanceListRequest;
use App\Http\Requests\DailyAttendanceUpdateRequest;
use App\Http\Resources\DailyAttendanceListResource;
use App\Models\DailyAttendance;
use App\Models\User;
use App\Models\WorkSite;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\Response;

class DailyAttendanceController extends Controller
{
    /**
     * @throws InvalidSubWorkSiteAttendanceException
     */
    public function store(int $employeeId, DailyAttendanceCreateRequest $request): JsonResponse
    {

        /** @var array{
         *     work_site_id : int,
         *     date_from : string | null,
         *     date_to : string | null
         * } $requestedData
         */
        $requestedData = $request->validated();
        User::query()->findOrFail($employeeId);

        $dateFrom = $requestedData['date_from'] ?? Carbon::today();
        $dateTo = $requestedData['date_to'] ?? Carbon::today();

        $alreadyHasDailyAttendance = DailyAttendance::query()
            ->where(
                column: 'employee_id',
                operator: '=',
                value: $employeeId)
            ->whereDate(column: 'date',
                operator: '>=',
                value: $dateFrom)
            ->whereDate(column: 'date',
                operator: '<=',
                value: $dateTo)
            ->exists();
        if ($alreadyHasDailyAttendance) {
            throw new InvalidSubWorkSiteAttendanceException('You already have a daily attendance for a work site', Response::HTTP_FORBIDDEN);
        }
        $workSite = WorkSite::query()->findOrFail($requestedData['work_site_id']);

        // test if work site is a sub-worksite
        if ($workSite->parent_work_site_id != null) {
            throw new InvalidSubWorkSiteAttendanceException('Cant Assign employee to work site', Response::HTTP_FORBIDDEN);
        }

        $dates = $this->getDates($dateFrom, $dateTo);
        $dataToSave = [];

        foreach ($dates as $date) {
            $dataToSave[] = [
                'employee_id' => $employeeId,
                'work_site_id' => $requestedData['work_site_id'],
                'date' => $date,
            ];
        }

        DailyAttendance::query()->insert($dataToSave);

        return ApiResponseHelper::sendSuccessResponse();
    }

    /**
     * @throws InvalidSubWorkSiteAttendanceException
     */
    public function update(int $employeeId, DailyAttendanceUpdateRequest $request): JsonResponse
    {

        /** @var array{
         *     work_site_id : int|null,
         *     date_from : string,
         *     date_to : string
         * } $requestedData
         */
        $requestedData = $request->validated();
        User::query()->findOrFail($employeeId);

        $dateFrom = $requestedData['date_from'];
        $dateTo = $requestedData['date_to'];

        $alreadyHasDailyAttendance = DailyAttendance::query()
            ->where(
                column: 'employee_id',
                operator: '=',
                value: $employeeId)
            ->whereDate(column: 'date',
                operator: '>=',
                value: $dateFrom)
            ->whereDate(column: 'date',
                operator: '<=',
                value: $dateTo)
            ->exists();
        if ($alreadyHasDailyAttendance) {
            throw new InvalidSubWorkSiteAttendanceException('You already have a daily attendance for a work site', Response::HTTP_FORBIDDEN);
        }

        $dates = $this->getDates($dateFrom, $dateTo);
        if (! isset($requestedData['work_site_id'])) {
            foreach ($dates as $date) {
                DailyAttendance::query()
                    ->where(column: 'employee_id', operator: '=', value: $employeeId)
                    ->whereDate(column: 'date', operator: '=', value: $date)
                    ->delete();
            }
        } else {

            $dataToSave = [];
            foreach ($dates as $date) {
                $dataToSave[] = [
                    'employee_id' => $employeeId,
                    'work_site_id' => $requestedData['work_site_id'],
                    'date' => $date,
                ];
            }
            $workSite = WorkSite::query()->findOrFail($requestedData['work_site_id']);

            // test if work site is a sub-worksite
            if ($workSite->parent_work_site_id != null) {
                throw new InvalidSubWorkSiteAttendanceException('Cant Assign employee to work site', Response::HTTP_FORBIDDEN);
            }

            DailyAttendance::query()->upsert(
                values: $dataToSave,
                uniqueBy: ['employee_id', 'work_site_id'],
                update: ['date', 'work_site_id']);
        }

        return ApiResponseHelper::sendSuccessResponse();
    }

    public function list(int $employeeId, DailyAttendanceListRequest $request): JsonResponse
    {
        /** @var array{
         *     employee_id : int | null,
         *     date_from : string | null,
         *     date_to : string | null
         * } $requestedData
         */
        $requestedData = $request->validated();

        User::query()->findOrFail($employeeId);

        $dateFrom = $requestedData['date_from'] ?? Carbon::today();
        $dateTo = $requestedData['date_to'] ?? Carbon::today();

        $result = DailyAttendance::query()
            ->where(
                column: 'employee_id',
                operator: '=',
                value: $requestedData['employee_id'] ?? $employeeId)
            ->whereDate(column: 'date',
                operator: '>=',
                value: $dateFrom)
            ->whereDate(column: 'date',
                operator: '<=',
                value: $dateTo)
            ->get();

        return ApiResponseHelper::sendSuccessResponse(new Result(DailyAttendanceListResource::collection($result)));
    }

    /**
     * @return array<string>
     */
    private function getDates(?string $from = null, ?string $to = null): array
    {
        $from = $from ? Carbon::parse($from) : Carbon::today();
        $to = $to ? Carbon::parse($to) : $from->copy();

        $dates = [];

        while ($from <= $to) {
            $dates[] = $from->toDateString();
            $from->addDay();
        }

        return $dates;
    }
}

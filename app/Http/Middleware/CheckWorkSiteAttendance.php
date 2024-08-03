<?php

namespace App\Http\Middleware;

use App\Exceptions\UserAttendanceException;
use App\Helpers\ApiResponse\ApiResponseHelper;
use App\Helpers\ApiResponse\ErrorResult;
use App\Models\DailyAttendance;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class CheckWorkSiteAttendance
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     *
     * @throws UserAttendanceException
     */
    public function handle(Request $request, Closure $next): Response
    {

        $authUser = Auth::user();

        // Validate the request inputs
        $validator = Validator::make($request->all(), [
            'work_site_id' => 'required|integer',
            'date' => 'required|date',
        ]);

        if ($validator->fails()) {
            // Handle validation failure
            return ApiResponseHelper::sendErrorResponse(new ErrorResult('invalid_parameters', Response::HTTP_UNPROCESSABLE_ENTITY));
        }

        if ($authUser && $authUser->hasRole('admin')) {
            return $next($request);
        }

        // Ensure $date is a valid date string
        $dateInput = $request->input('date');
        $date = null;
        if (is_string($dateInput)) {
            $date = Carbon::parse($dateInput)->toDateString();
        }

        $doesUserPresentAtWorkSite = DailyAttendance::query()
            ->where('employee_id', $authUser?->id)
            ->where('work_site_id', $request->input('work_site_id'))
            ->whereDate('date', $date)
            ->exists();

        if (! $doesUserPresentAtWorkSite) {
            throw new UserAttendanceException('Employee attendance does not present at work site');
        }

        return $next($request);
    }
}

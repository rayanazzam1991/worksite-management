<?php

namespace App\Exceptions;

use App\Helpers\ApiResponse\ApiResponseHelper;
use App\Helpers\ApiResponse\ErrorResult;
use Exception;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class UserAttendanceException extends Exception
{
    public function render(): JsonResponse
    {
        return ApiResponseHelper::sendErrorResponse(new ErrorResult(
            message: $this->getMessage(),
            code: Response::HTTP_FORBIDDEN,
            isOk: false
        ));
    }
}

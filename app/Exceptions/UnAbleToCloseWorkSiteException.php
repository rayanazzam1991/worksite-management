<?php

namespace App\Exceptions;

use App\Helpers\ApiResponse\ApiResponseHelper;
use App\Helpers\ApiResponse\ErrorResult;
use Exception;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UnAbleToCloseWorkSiteException extends Exception
{
    /**
     * Report the exception.
     */
    public function report(): void
    {
        // ...
    }

    /**
     * Render the exception into an HTTP response.
     */
    public function render(Request $request): \Illuminate\Http\JsonResponse
    {
        return ApiResponseHelper::sendErrorResponse(new ErrorResult(
            message: $this->getMessage(),
            code: Response::HTTP_CONFLICT,
            isOk: false
        ));
    }
}

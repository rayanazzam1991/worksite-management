<?php

namespace App\Exceptions;

use App\Helpers\ApiResponse\ApiResponseHelper;
use App\Helpers\ApiResponse\ErrorResult;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UnAbleToDeleteCustomerException extends Exception
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
    public function render(Request $request): JsonResponse
    {
        return ApiResponseHelper::sendErrorResponse(new ErrorResult(
            message: $this->getMessage(),
            code: Response::HTTP_CONFLICT,
            isOk: false
        ));
    }
}

<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse\ApiResponseHelper;
use App\Helpers\ApiResponse\Result;
use App\Http\Requests\LoginRequest;
use App\Http\Resources\LoginResource;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use stdClass;

class LoginController extends Controller
{
    /**
     * @throws AuthenticationException
     */
    public function __invoke(LoginRequest $request): JsonResponse
    {

        /**
         * @var array{
         *     user_name : string,
         *     password:string
         * } $requestedData
         */
        $requestedData = $request->validated();

        /**
         * @var stdClass{
         *     user:User,
         *     token:string
         * } $result
         */

        // TODO:consider replace stdClass with DTO
        $result = new stdClass();

        $user = User::query()->where('phone', $requestedData['user_name'])->first();

        if (! $user || ! Hash::check($requestedData['password'], $user->password)) {
            throw new AuthenticationException();
        }

        $result->user = $user;
        $result->token = $user->createToken('token')->plainTextToken;

        return ApiResponseHelper::sendSuccessResponse(new Result(LoginResource::make($result)));

    }
}

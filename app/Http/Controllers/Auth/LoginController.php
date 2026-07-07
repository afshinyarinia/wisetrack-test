<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\UserResource;
use App\Modules\Auth\Actions\LoginUserAction;
use App\Modules\Auth\Data\LoginUserData;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\UnauthorizedException;

final class LoginController extends Controller
{
    public function __invoke(LoginRequest $request, LoginUserAction $loginUser): JsonResponse
    {
        try {
            $result = $loginUser->execute(LoginUserData::fromRequest($request));
        } catch (UnauthorizedException $exception) {
            return response()->json(['message' => $exception->getMessage()], 401);
        }

        return response()->json([
            'data' => [
                'user' => new UserResource($result['user']),
                'token' => $result['token'],
            ],
        ]);
    }
}

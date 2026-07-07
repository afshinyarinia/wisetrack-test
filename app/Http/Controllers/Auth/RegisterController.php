<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Modules\Auth\Actions\RegisterUserAction;
use App\Modules\Auth\Data\RegisterUserData;
use Illuminate\Http\JsonResponse;

final class RegisterController extends Controller
{
    public function __invoke(RegisterRequest $request, RegisterUserAction $registerUser): JsonResponse
    {
        $result = $registerUser->execute(RegisterUserData::fromRequest($request));

        return response()->json([
            'data' => [
                'user' => new UserResource($result['user']),
                'token' => $result['token'],
            ],
        ], 201);
    }
}

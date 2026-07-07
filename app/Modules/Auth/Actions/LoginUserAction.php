<?php

namespace App\Modules\Auth\Actions;

use App\Models\User;
use App\Modules\Auth\Data\LoginUserData;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\UnauthorizedException;

final class LoginUserAction
{
    public function execute(LoginUserData $data): array
    {
        $user = User::query()->where('email', $data->email)->first();

        if (! $user || ! Hash::check($data->password, $user->password)) {
            throw new UnauthorizedException('Invalid credentials.');
        }

        return [
            'user' => $user,
            'token' => $user->createToken('api')->plainTextToken,
        ];
    }
}

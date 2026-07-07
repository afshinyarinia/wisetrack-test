<?php

namespace App\Modules\Auth\Actions;

use App\Jobs\SendWelcomeEmailJob;
use App\Models\User;
use App\Modules\Auth\Data\RegisterUserData;
use Illuminate\Support\Facades\Hash;

final class RegisterUserAction
{
    public function execute(RegisterUserData $data): array
    {
        $user = User::query()->create([
            'name' => $data->name,
            'email' => $data->email,
            'password' => Hash::make($data->password),
        ]);

        SendWelcomeEmailJob::dispatch($user->id);

        return [
            'user' => $user,
            'token' => $user->createToken('api')->plainTextToken,
        ];
    }
}

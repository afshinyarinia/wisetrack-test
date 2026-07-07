<?php

namespace App\Modules\Auth\Data;

use App\Http\Requests\Auth\LoginRequest;

final readonly class LoginUserData
{
    public function __construct(
        public string $email,
        public string $password,
    ) {}

    public static function fromRequest(LoginRequest $request): self
    {
        return new self(
            email: $request->string('email')->lower()->toString(),
            password: $request->string('password')->toString(),
        );
    }
}

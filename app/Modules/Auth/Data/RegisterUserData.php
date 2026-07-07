<?php

namespace App\Modules\Auth\Data;

use App\Http\Requests\Auth\RegisterRequest;

final readonly class RegisterUserData
{
    public function __construct(
        public string $name,
        public string $email,
        public string $password,
    ) {}

    public static function fromRequest(RegisterRequest $request): self
    {
        return new self(
            name: $request->string('name')->toString(),
            email: $request->string('email')->lower()->toString(),
            password: $request->string('password')->toString(),
        );
    }
}

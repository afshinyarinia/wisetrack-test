<?php

namespace App\Modules\Analytics\ValueObjects;

final readonly class VisitorIdentity
{
    public function __construct(
        public string $type,
        public string $hash,
    ) {}

    public static function forUser(int $userId): self
    {
        return new self('user', hash('sha256', 'user:'.$userId));
    }

    public static function forGuest(string $ip, ?string $userAgent): self
    {
        return new self('guest', hash('sha256', 'guest:'.$ip.'|'.($userAgent ?? '')));
    }
}

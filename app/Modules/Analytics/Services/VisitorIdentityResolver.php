<?php

namespace App\Modules\Analytics\Services;

use App\Models\User;
use App\Modules\Analytics\ValueObjects\VisitorIdentity;

final class VisitorIdentityResolver
{
    public function resolve(?User $user, string $ip, ?string $userAgent): VisitorIdentity
    {
        if ($user) {
            return VisitorIdentity::forUser($user->id);
        }

        return VisitorIdentity::forGuest($ip, $userAgent);
    }
}

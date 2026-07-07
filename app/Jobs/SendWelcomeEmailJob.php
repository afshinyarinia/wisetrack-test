<?php

namespace App\Jobs;

use App\Mail\WelcomeUserMail;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

final class SendWelcomeEmailJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 30;

    public function __construct(
        public readonly int $userId,
    ) {}

    public function handle(): void
    {
        $user = User::query()->findOrFail($this->userId);

        Mail::to($user->email)->send(new WelcomeUserMail($user));
    }
}

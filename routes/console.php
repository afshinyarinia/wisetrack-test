<?php

use App\Models\User;
use App\Modules\Posts\Models\Post;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('posts:seed {--count=100 : Number of fake posts to create}', function (): int {
    $count = (int) $this->option('count');

    if ($count < 1) {
        $this->error('The count option must be at least 1.');

        return self::FAILURE;
    }

    $author = User::query()->first() ?? User::factory()->create([
        'name' => 'Demo Author',
        'email' => 'author@example.com',
    ]);

    Post::factory()
        ->count($count)
        ->for($author, 'author')
        ->create();

    $this->info("Seeded {$count} posts.");

    return self::SUCCESS;
})->purpose('Seed fake posts for local testing');

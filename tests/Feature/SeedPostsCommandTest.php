<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Posts\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeedPostsCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_posts_seed_command_creates_one_hundred_posts_by_default(): void
    {
        $this->artisan('posts:seed')
            ->expectsOutput('Seeded 100 posts.')
            ->assertSuccessful();

        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('posts', 100);
    }

    public function test_posts_seed_command_accepts_a_custom_count(): void
    {
        $author = User::factory()->create();

        $this->artisan('posts:seed --count=7')
            ->expectsOutput('Seeded 7 posts.')
            ->assertSuccessful();

        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('posts', 7);
        $this->assertSame($author->id, Post::query()->firstOrFail()->user_id);
    }
}

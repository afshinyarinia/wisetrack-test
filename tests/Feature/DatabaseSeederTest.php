<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Analytics\Models\PostView;
use App\Modules\Posts\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_creates_posts_and_analytics_data(): void
    {
        $this->seed();

        $this->assertDatabaseCount('users', 2);
        $this->assertDatabaseCount('posts', 100);
        $this->assertGreaterThan(0, PostView::query()->count());
        $this->assertTrue(PostView::query()->whereNotNull('user_id')->exists());
        $this->assertTrue(PostView::query()->whereNull('user_id')->exists());
        $this->assertTrue(
            PostView::query()
                ->whereBetween('viewed_date', ['2026-01-01', '2026-01-31'])
                ->exists()
        );
        $this->assertFalse(PostView::query()->where('viewed_date', '>', '2026-01-31')->exists());

        $firstPost = Post::query()->oldest('id')->firstOrFail();
        $viewer = User::query()->where('email', 'viewer@example.com')->firstOrFail();

        $this->assertTrue(
            PostView::query()
                ->where('post_id', $firstPost->id)
                ->where('user_id', $viewer->id)
                ->exists()
        );
    }
}

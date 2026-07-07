<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Analytics\Models\PostView;
use App\Modules\Posts\Models\Post;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AnalyticsApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_daily_analytics_returns_counts_and_zero_days(): void
    {
        $dashboardUser = User::factory()->create();
        $post = Post::factory()->create(['title' => 'Analytics Post']);
        $registeredViewer = User::factory()->create();

        $this->createView($post, $registeredViewer, '2026-01-01', 'user-one');
        $this->createView($post, null, '2026-01-03', 'guest-one');
        $this->createView($post, null, '2026-01-03', 'guest-two');

        Sanctum::actingAs($dashboardUser);

        $this->getJson("/api/posts/{$post->id}/analytics/daily?from=2026-01-01&to=2026-01-03")
            ->assertOk()
            ->assertJsonPath('data.post_id', $post->id)
            ->assertJsonPath('data.analytics.0.date', '2026-01-01')
            ->assertJsonPath('data.analytics.0.total_views', 1)
            ->assertJsonPath('data.analytics.0.registered_users', 1)
            ->assertJsonPath('data.analytics.1.date', '2026-01-02')
            ->assertJsonPath('data.analytics.1.total_views', 0)
            ->assertJsonPath('data.analytics.2.date', '2026-01-03')
            ->assertJsonPath('data.analytics.2.guest_users', 2)
            ->assertJsonPath('data.meta.total_days', 3)
            ->assertJsonPath('data.meta.total_views', 3);
    }

    public function test_top_viewed_posts_are_sorted(): void
    {
        $user = User::factory()->create();
        $first = Post::factory()->create(['title' => 'First']);
        $second = Post::factory()->create(['title' => 'Second']);

        $this->createView($first, null, '2026-01-01', 'guest-one');
        $this->createView($second, null, '2026-01-01', 'guest-two');
        $this->createView($second, $user, '2026-01-01', 'user-one');

        $this->getJson('/api/posts/top-viewed?from=2026-01-01&to=2026-01-07')
            ->assertOk()
            ->assertJsonPath('data.0.title', 'Second')
            ->assertJsonPath('data.0.total_views', 2)
            ->assertJsonPath('data.1.title', 'First')
            ->assertJsonPath('data.1.total_views', 1);
    }

    private function createView(Post $post, ?User $user, string $date, string $visitorKey): void
    {
        $viewedAt = CarbonImmutable::parse($date)->setTime(12, 0);

        PostView::query()->create([
            'post_id' => $post->id,
            'user_id' => $user?->id,
            'visitor_hash' => hash('sha256', $visitorKey),
            'ip_address' => '203.0.113.'.random_int(1, 200),
            'user_agent' => 'Feature Test Browser',
            'viewed_date' => $viewedAt->toDateString(),
            'viewed_at' => $viewedAt,
        ]);
    }
}

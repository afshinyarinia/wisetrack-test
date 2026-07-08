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

    public function test_analytics_endpoints_require_authentication(): void
    {
        $post = Post::factory()->create();

        $this->getJson("/api/posts/{$post->id}/analytics/daily")->assertUnauthorized();
        $this->getJson("/api/posts/{$post->id}/analytics/summary")->assertUnauthorized();
    }

    public function test_daily_analytics_validates_date_range(): void
    {
        $post = Post::factory()->create();

        Sanctum::actingAs(User::factory()->create());

        $this->getJson("/api/posts/{$post->id}/analytics/daily?from=not-a-date&to=2026-01-03")
            ->assertUnprocessable()
            ->assertJsonValidationErrors('from');

        $this->getJson("/api/posts/{$post->id}/analytics/daily?from=2026-01-04&to=2026-01-03")
            ->assertUnprocessable()
            ->assertJsonValidationErrors('from');
    }

    public function test_daily_analytics_is_scoped_to_requested_post_and_date_range(): void
    {
        $dashboardUser = User::factory()->create();
        $post = Post::factory()->create();
        $otherPost = Post::factory()->create();

        $this->createView($post, null, '2026-01-01', 'target-day-one');
        $this->createView($post, null, '2026-01-04', 'target-outside-range');
        $this->createView($otherPost, null, '2026-01-01', 'other-post');

        Sanctum::actingAs($dashboardUser);

        $this->getJson("/api/posts/{$post->id}/analytics/daily?from=2026-01-01&to=2026-01-03")
            ->assertOk()
            ->assertJsonPath('data.meta.total_views', 1)
            ->assertJsonPath('data.analytics.0.total_views', 1)
            ->assertJsonPath('data.analytics.1.total_views', 0)
            ->assertJsonPath('data.analytics.2.total_views', 0);
    }

    public function test_summary_analytics_includes_daily_rows_and_meta(): void
    {
        $dashboardUser = User::factory()->create();
        $post = Post::factory()->create(['title' => 'Analytics Post']);
        $registeredViewer = User::factory()->create();

        $this->createView($post, $registeredViewer, '2026-01-01', 'user-one');
        $this->createView($post, null, '2026-01-02', 'guest-one');

        Sanctum::actingAs($dashboardUser);

        $this->getJson("/api/posts/{$post->id}/analytics/summary?from=2026-01-01&to=2026-01-02")
            ->assertOk()
            ->assertJsonPath('data.post_id', $post->id)
            ->assertJsonPath('data.title', 'Analytics Post')
            ->assertJsonPath('data.analytics.0.date', '2026-01-01')
            ->assertJsonPath('data.analytics.0.registered_users', 1)
            ->assertJsonPath('data.analytics.1.date', '2026-01-02')
            ->assertJsonPath('data.analytics.1.guest_users', 1)
            ->assertJsonPath('data.meta.total_days', 2)
            ->assertJsonPath('data.meta.total_views', 2);
    }

    public function test_missing_post_analytics_returns_not_found(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/posts/999999/analytics/daily')->assertNotFound();
        $this->getJson('/api/posts/999999/analytics/summary')->assertNotFound();
    }

    public function test_top_viewed_posts_match_dashboard_contract(): void
    {
        $user = User::factory()->create();
        $author = User::factory()->create(['name' => 'Report Author']);
        $first = Post::factory()->for($author, 'author')->create(['title' => 'First']);
        $second = Post::factory()->for($author, 'author')->create(['title' => 'Second']);

        $this->createView($first, null, '2026-01-01', 'guest-one');
        $this->createView($second, null, '2026-01-01', 'guest-two');
        $this->createView($second, $user, '2026-01-01', 'user-one');

        $this->getJson('/api/posts/top-viewed?from=2026-01-01&to=2026-01-07')
            ->assertOk()
            ->assertJsonPath('data.0.rank', 1)
            ->assertJsonPath('data.0.post_id', $second->id)
            ->assertJsonPath('data.0.title', 'Second')
            ->assertJsonPath('data.0.author', 'Report Author')
            ->assertJsonPath('data.0.total_views', 2)
            ->assertJsonPath('data.0.unique_users', 2)
            ->assertJsonPath('data.0.trend', 'stable')
            ->assertJsonPath('data.1.rank', 2)
            ->assertJsonPath('data.1.post_id', $first->id)
            ->assertJsonPath('data.1.title', 'First')
            ->assertJsonPath('data.1.total_views', 1)
            ->assertJsonPath('meta.total_posts_analyzed', 2)
            ->assertJsonPath('meta.period_days', 7)
            ->assertJsonPath('meta.average_views_per_post', 1.5);
    }

    public function test_top_viewed_posts_validate_limit_and_date_range(): void
    {
        $this->getJson('/api/posts/top-viewed?limit=0')
            ->assertUnprocessable()
            ->assertJsonValidationErrors('limit');

        $this->getJson('/api/posts/top-viewed?from=2026-01-04&to=2026-01-03')
            ->assertUnprocessable()
            ->assertJsonValidationErrors('from');
    }

    public function test_top_viewed_posts_respect_limit_and_date_range(): void
    {
        $author = User::factory()->create(['name' => 'Report Author']);
        $first = Post::factory()->for($author, 'author')->create(['title' => 'First']);
        $second = Post::factory()->for($author, 'author')->create(['title' => 'Second']);
        $outsideRange = Post::factory()->for($author, 'author')->create(['title' => 'Outside Range']);

        $this->createView($first, null, '2026-01-01', 'guest-one');
        $this->createView($second, null, '2026-01-01', 'guest-two');
        $this->createView($second, null, '2026-01-02', 'guest-three');
        $this->createView($outsideRange, null, '2026-01-05', 'guest-four');
        $this->createView($outsideRange, null, '2026-01-05', 'guest-five');
        $this->createView($outsideRange, null, '2026-01-05', 'guest-six');

        $this->getJson('/api/posts/top-viewed?from=2026-01-01&to=2026-01-02&limit=1')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.post_id', $second->id)
            ->assertJsonPath('data.0.total_views', 2)
            ->assertJsonPath('meta.total_posts_analyzed', 1);
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

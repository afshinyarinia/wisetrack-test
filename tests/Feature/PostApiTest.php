<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Analytics\Models\PostView;
use App\Modules\Posts\Models\Post;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PostApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_post(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $this->postJson('/api/posts', [
            'title' => 'Laravel Analytics',
            'content' => 'A practical analytics implementation.',
        ])
            ->assertCreated()
            ->assertJsonPath('data.title', 'Laravel Analytics')
            ->assertJsonPath('data.author.id', $user->id);

        $this->assertDatabaseHas('posts', [
            'user_id' => $user->id,
            'title' => 'Laravel Analytics',
        ]);
    }

    public function test_guest_cannot_create_post(): void
    {
        $this->postJson('/api/posts', [
            'title' => 'Laravel Analytics',
            'content' => 'A practical analytics implementation.',
        ])->assertUnauthorized();
    }

    public function test_posts_are_paginated(): void
    {
        Post::factory()->count(20)->create();

        $this->getJson('/api/posts')
            ->assertOk()
            ->assertJsonCount(15, 'data')
            ->assertJsonPath('meta.per_page', 15)
            ->assertJsonStructure(['links', 'meta']);
    }

    public function test_post_list_reports_distinct_unique_view_count(): void
    {
        $post = Post::factory()->create();

        PostView::query()->create([
            'post_id' => $post->id,
            'user_id' => null,
            'visitor_hash' => hash('sha256', 'returning-guest'),
            'ip_address' => '203.0.113.10',
            'user_agent' => 'Feature Test Browser',
            'viewed_date' => '2026-01-01',
            'viewed_at' => CarbonImmutable::parse('2026-01-01 12:00:00'),
        ]);

        PostView::query()->create([
            'post_id' => $post->id,
            'user_id' => null,
            'visitor_hash' => hash('sha256', 'returning-guest'),
            'ip_address' => '203.0.113.10',
            'user_agent' => 'Feature Test Browser',
            'viewed_date' => '2026-01-02',
            'viewed_at' => CarbonImmutable::parse('2026-01-02 12:00:00'),
        ]);

        $this->getJson('/api/posts')
            ->assertOk()
            ->assertJsonPath('data.0.views_count', 2)
            ->assertJsonPath('data.0.unique_views_count', 1);
    }

    public function test_post_show_tracks_view_once_per_user_per_day(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        Sanctum::actingAs($user);

        $this->getJson("/api/posts/{$post->id}")
            ->assertOk()
            ->assertJsonPath('data.views_count', 1)
            ->assertJsonPath('data.unique_views_count', 1);

        $this->getJson("/api/posts/{$post->id}")->assertOk();

        $this->assertDatabaseCount('post_views', 1);
        $this->assertSame($user->id, PostView::query()->firstOrFail()->user_id);
    }

    public function test_guest_duplicate_view_is_recorded_once_per_day(): void
    {
        $post = Post::factory()->create();

        $this->withHeaders(['User-Agent' => 'Feature Test Browser'])
            ->getJson("/api/posts/{$post->id}", ['REMOTE_ADDR' => '203.0.113.10'])
            ->assertOk();

        $this->withHeaders(['User-Agent' => 'Feature Test Browser'])
            ->getJson("/api/posts/{$post->id}", ['REMOTE_ADDR' => '203.0.113.10'])
            ->assertOk();

        $this->assertDatabaseCount('post_views', 1);
    }

    /*
    |--------------------------------------------------------------------------
    | Additional coverage beyond the assignment requirements
    |--------------------------------------------------------------------------
    */

    public function test_post_creation_validates_required_fields_and_title_length(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/posts', [
            'title' => '',
            'content' => '',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['title', 'content']);

        $this->postJson('/api/posts', [
            'title' => str_repeat('a', 256),
            'content' => 'A practical analytics implementation.',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('title');
    }

    public function test_missing_post_returns_not_found(): void
    {
        $this->getJson('/api/posts/999999')->assertNotFound();
    }

    public function test_same_visitor_is_recorded_again_on_a_different_day(): void
    {
        $post = Post::factory()->create();

        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-01-01 12:00:00'));
        $this->withHeaders(['User-Agent' => 'Feature Test Browser'])
            ->getJson("/api/posts/{$post->id}", ['REMOTE_ADDR' => '203.0.113.10'])
            ->assertOk();

        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-01-02 12:00:00'));
        $this->withHeaders(['User-Agent' => 'Feature Test Browser'])
            ->getJson("/api/posts/{$post->id}", ['REMOTE_ADDR' => '203.0.113.10'])
            ->assertOk();

        CarbonImmutable::setTestNow();

        $this->assertDatabaseCount('post_views', 2);
        $this->assertDatabaseHas('post_views', ['post_id' => $post->id, 'viewed_date' => '2026-01-01']);
        $this->assertDatabaseHas('post_views', ['post_id' => $post->id, 'viewed_date' => '2026-01-02']);
    }
}

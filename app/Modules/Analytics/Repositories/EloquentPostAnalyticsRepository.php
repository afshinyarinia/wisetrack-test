<?php

namespace App\Modules\Analytics\Repositories;

use App\Modules\Analytics\Models\PostView;
use App\Modules\Analytics\ValueObjects\DateRange;
use App\Modules\Posts\Models\Post;
use Illuminate\Support\Collection;

final class EloquentPostAnalyticsRepository implements PostAnalyticsRepositoryInterface
{
    public function dailyRows(Post $post, DateRange $range): Collection
    {
        return PostView::query()
            ->selectRaw('viewed_date as date')
            ->selectRaw('COUNT(*) as total_views')
            ->selectRaw('COUNT(DISTINCT visitor_hash) as unique_users')
            ->selectRaw('COUNT(DISTINCT CASE WHEN user_id IS NOT NULL THEN visitor_hash END) as registered_users')
            ->selectRaw('COUNT(DISTINCT CASE WHEN user_id IS NULL THEN visitor_hash END) as guest_users')
            ->where('post_id', $post->id)
            ->whereBetween('viewed_date', [$range->from->toDateString(), $range->to->toDateString()])
            ->groupBy('viewed_date')
            ->orderBy('viewed_date')
            ->get()
            ->keyBy('date');
    }

    public function totalUniqueVisitors(Post $post, DateRange $range): int
    {
        return (int) PostView::query()
            ->where('post_id', $post->id)
            ->whereBetween('viewed_date', [$range->from->toDateString(), $range->to->toDateString()])
            ->distinct('visitor_hash')
            ->count('visitor_hash');
    }

    public function topViewed(DateRange $range, int $limit): Collection
    {
        return Post::query()
            ->select('posts.*')
            ->selectRaw('COUNT(post_views.id) as total_views')
            ->selectRaw('COUNT(DISTINCT post_views.visitor_hash) as unique_users')
            ->join('post_views', 'post_views.post_id', '=', 'posts.id')
            ->whereBetween('post_views.viewed_date', [$range->from->toDateString(), $range->to->toDateString()])
            ->with('author')
            ->groupBy([
                'posts.id',
                'posts.user_id',
                'posts.title',
                'posts.content',
                'posts.image',
                'posts.deleted_at',
                'posts.created_at',
                'posts.updated_at',
            ])
            ->orderByDesc('total_views')
            ->limit($limit)
            ->get();
    }
}

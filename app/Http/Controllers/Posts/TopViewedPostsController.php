<?php

namespace App\Http\Controllers\Posts;

use App\Http\Controllers\Controller;
use App\Http\Requests\Analytics\TopViewedPostsRequest;
use App\Http\Resources\TopViewedPostResource;
use App\Modules\Analytics\Actions\GetTopViewedPostsAction;
use App\Modules\Analytics\ValueObjects\DateRange;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class TopViewedPostsController extends Controller
{
    public function __invoke(TopViewedPostsRequest $request, GetTopViewedPostsAction $action): AnonymousResourceCollection
    {
        $range = $request->dateRange();
        $posts = $action->execute($range, (int) $request->integer('limit', 10));

        $posts->values()->each(function ($post, int $index): void {
            $post->rank = $index + 1;
            $post->trend = 'stable';
        });

        return TopViewedPostResource::collection($posts)
            ->additional([
                'meta' => $this->meta($posts, $range),
            ]);
    }

    private function meta($posts, DateRange $range): array
    {
        $totalPosts = $posts->count();
        $totalViews = (int) $posts->sum('total_views');

        return [
            'total_posts_analyzed' => $totalPosts,
            'period_days' => $range->daysCount(),
            'average_views_per_post' => $totalPosts > 0 ? round($totalViews / $totalPosts, 2) : 0,
        ];
    }
}

<?php

namespace App\Http\Controllers\Analytics;

use App\Http\Controllers\Controller;
use App\Http\Requests\Analytics\DailyAnalyticsRequest;
use App\Http\Resources\PostAnalyticsSummaryResource;
use App\Modules\Analytics\Actions\GetPostAnalyticsSummaryAction;
use App\Modules\Posts\Models\Post;

final class PostAnalyticsSummaryController extends Controller
{
    public function __invoke(DailyAnalyticsRequest $request, Post $post, GetPostAnalyticsSummaryAction $action): PostAnalyticsSummaryResource
    {
        return new PostAnalyticsSummaryResource($action->execute($post, $request->dateRange()));
    }
}

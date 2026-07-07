<?php

namespace App\Http\Controllers\Analytics;

use App\Http\Controllers\Controller;
use App\Http\Requests\Analytics\DailyAnalyticsRequest;
use App\Http\Resources\DailyPostAnalyticsResource;
use App\Modules\Analytics\Actions\GetDailyPostAnalyticsAction;
use App\Modules\Posts\Models\Post;

final class DailyPostAnalyticsController extends Controller
{
    public function __invoke(DailyAnalyticsRequest $request, Post $post, GetDailyPostAnalyticsAction $action): DailyPostAnalyticsResource
    {
        return new DailyPostAnalyticsResource($action->execute($post, $request->dateRange()));
    }
}

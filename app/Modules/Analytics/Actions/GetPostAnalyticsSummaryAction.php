<?php

namespace App\Modules\Analytics\Actions;

use App\Modules\Analytics\ValueObjects\DateRange;
use App\Modules\Posts\Models\Post;

final class GetPostAnalyticsSummaryAction
{
    public function __construct(
        private readonly GetDailyPostAnalyticsAction $dailyAnalyticsAction,
    ) {}

    public function execute(Post $post, DateRange $range): array
    {
        $daily = $this->dailyAnalyticsAction->execute($post, $range);

        return [
            'post' => $post,
            'period' => $daily['period'],
            'meta' => $daily['meta'],
        ];
    }
}

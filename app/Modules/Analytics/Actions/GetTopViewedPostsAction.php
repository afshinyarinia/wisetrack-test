<?php

namespace App\Modules\Analytics\Actions;

use App\Modules\Analytics\Repositories\PostAnalyticsRepositoryInterface;
use App\Modules\Analytics\ValueObjects\DateRange;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

final class GetTopViewedPostsAction
{
    public function __construct(
        private readonly PostAnalyticsRepositoryInterface $analyticsRepository,
    ) {}

    public function execute(DateRange $range, int $limit): Collection
    {
        $cacheKey = sprintf(
            'analytics:top-viewed:%s:%s:%d',
            $range->from->toDateString(),
            $range->to->toDateString(),
            $limit,
        );

        return Cache::remember(
            $cacheKey,
            now()->addMinute(),
            fn (): Collection => $this->analyticsRepository->topViewed($range, $limit),
        );
    }
}

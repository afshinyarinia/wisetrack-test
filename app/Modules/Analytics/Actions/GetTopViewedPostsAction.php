<?php

namespace App\Modules\Analytics\Actions;

use App\Modules\Analytics\Repositories\PostAnalyticsRepositoryInterface;
use App\Modules\Analytics\ValueObjects\DateRange;
use Illuminate\Support\Collection;

final class GetTopViewedPostsAction
{
    public function __construct(
        private readonly PostAnalyticsRepositoryInterface $analyticsRepository,
    ) {}

    public function execute(DateRange $range, int $limit): Collection
    {
        return $this->analyticsRepository->topViewed($range, $limit);
    }
}

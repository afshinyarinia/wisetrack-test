<?php

namespace App\Modules\Analytics\Actions;

use App\Modules\Analytics\Repositories\PostAnalyticsRepositoryInterface;
use App\Modules\Analytics\Services\AnalyticsMetaCalculator;
use App\Modules\Analytics\ValueObjects\DateRange;
use App\Modules\Posts\Models\Post;
use Illuminate\Support\Collection;

final class GetDailyPostAnalyticsAction
{
    public function __construct(
        private readonly PostAnalyticsRepositoryInterface $analyticsRepository,
        private readonly AnalyticsMetaCalculator $metaCalculator,
    ) {}

    public function execute(Post $post, DateRange $range): array
    {
        $rowsByDate = $this->analyticsRepository->dailyRows($post, $range);
        $rows = $this->fillMissingDates($rowsByDate, $range);
        $totalUniqueUsers = $this->analyticsRepository->totalUniqueVisitors($post, $range);

        return [
            'post' => $post,
            'period' => [
                'from' => $range->from->toDateString(),
                'to' => $range->to->toDateString(),
            ],
            'analytics' => $rows->values(),
            'meta' => $this->metaCalculator->calculate($rows, $range, $totalUniqueUsers),
        ];
    }

    private function fillMissingDates(Collection $rowsByDate, DateRange $range): Collection
    {
        $rows = collect();
        $current = $range->from;

        while ($current->lessThanOrEqualTo($range->to)) {
            $date = $current->toDateString();
            $row = $rowsByDate->get($date);

            $rows->push([
                'date' => $date,
                'unique_users' => (int) ($row->unique_users ?? 0),
                'total_views' => (int) ($row->total_views ?? 0),
                'registered_users' => (int) ($row->registered_users ?? 0),
                'guest_users' => (int) ($row->guest_users ?? 0),
            ]);

            $current = $current->addDay();
        }

        return $rows;
    }
}

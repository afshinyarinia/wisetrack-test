<?php

namespace App\Modules\Analytics\Services;

use App\Modules\Analytics\ValueObjects\DateRange;
use Illuminate\Support\Collection;

final class AnalyticsMetaCalculator
{
    public function __construct(
        private readonly TrendCalculator $trendCalculator,
    ) {}

    public function calculate(Collection $rows, DateRange $range): array
    {
        $totalViews = (int) $rows->sum('total_views');
        $totalUniqueUsers = (int) $rows->sum('unique_users');
        $peak = $rows->sortByDesc('unique_users')->first();

        return [
            'total_days' => $range->daysCount(),
            'total_unique_users' => $totalUniqueUsers,
            'total_views' => $totalViews,
            'average_daily_users' => round($totalUniqueUsers / max($range->daysCount(), 1), 2),
            'peak_day' => $peak['date'] ?? null,
            'peak_users' => (int) ($peak['unique_users'] ?? 0),
            ...$this->trendCalculator->calculate($rows),
        ];
    }
}

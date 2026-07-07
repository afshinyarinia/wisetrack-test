<?php

namespace App\Modules\Analytics\Services;

use Illuminate\Support\Collection;

final class TrendCalculator
{
    public function calculate(Collection $rows): array
    {
        if ($rows->count() < 2) {
            return ['trend' => 'stable', 'trend_percentage' => 0.0];
        }

        $first = (int) $rows->first()['unique_users'];
        $last = (int) $rows->last()['unique_users'];

        if ($first === 0 && $last === 0) {
            return ['trend' => 'stable', 'trend_percentage' => 0.0];
        }

        if ($first === 0) {
            return ['trend' => 'upward', 'trend_percentage' => 100.0];
        }

        $percentage = round((($last - $first) / $first) * 100, 2);

        return [
            'trend' => $percentage > 0 ? 'upward' : ($percentage < 0 ? 'downward' : 'stable'),
            'trend_percentage' => abs($percentage),
        ];
    }
}

<?php

namespace App\Modules\Analytics\Services;

use App\Modules\Analytics\ValueObjects\DateRange;
use DateTimeInterface;
use Illuminate\Support\Facades\Cache;

final class TopViewedPostsCache
{
    private const VERSION_KEY = 'analytics:top-viewed:version';
    private const INVALIDATION_THROTTLE_KEY = 'analytics:top-viewed:invalidation-throttle';

    public function key(DateRange $range, int $limit): string
    {
        return sprintf(
            'analytics:top-viewed:v%d:%s:%s:%d',
            $this->version(),
            $range->from->toDateString(),
            $range->to->toDateString(),
            $limit,
        );
    }

    public function ttl(): DateTimeInterface
    {
        return now()->addSeconds($this->ttlSeconds());
    }

    public function invalidate(): void
    {
        if (! Cache::add(self::INVALIDATION_THROTTLE_KEY, true, $this->ttl())) {
            return;
        }

        Cache::add(self::VERSION_KEY, 1);
        Cache::increment(self::VERSION_KEY);
    }

    private function version(): int
    {
        return (int) Cache::get(self::VERSION_KEY, 1);
    }

    private function ttlSeconds(): int
    {
        return max(1, (int) config('analytics.cache.top_viewed_ttl_seconds', 60));
    }
}

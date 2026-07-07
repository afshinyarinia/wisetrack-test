<?php

namespace App\Modules\Analytics\Repositories;

use App\Modules\Analytics\ValueObjects\DateRange;
use App\Modules\Posts\Models\Post;
use Illuminate\Support\Collection;

interface PostAnalyticsRepositoryInterface
{
    public function dailyRows(Post $post, DateRange $range): Collection;

    public function topViewed(DateRange $range, int $limit): Collection;
}

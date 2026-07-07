<?php

namespace App\Modules\Analytics\Repositories;

use App\Modules\Analytics\Data\TrackPostViewData;

interface PostViewRepositoryInterface
{
    public function recordUniqueDailyView(TrackPostViewData $data): void;
}

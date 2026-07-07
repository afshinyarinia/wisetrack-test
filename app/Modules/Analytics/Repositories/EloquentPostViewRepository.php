<?php

namespace App\Modules\Analytics\Repositories;

use App\Modules\Analytics\Data\TrackPostViewData;
use App\Modules\Analytics\Models\PostView;

final class EloquentPostViewRepository implements PostViewRepositoryInterface
{
    public function recordUniqueDailyView(TrackPostViewData $data): void
    {
        PostView::query()->insertOrIgnore([
            'post_id' => $data->postId,
            'user_id' => $data->userId,
            'visitor_hash' => $data->visitorHash,
            'ip_address' => $data->ipAddress,
            'user_agent' => $data->userAgent,
            'viewed_date' => $data->viewedDate(),
            'viewed_at' => $data->viewedAt,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}

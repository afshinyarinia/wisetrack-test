<?php

namespace App\Modules\Posts\Actions;

use App\Models\User;
use App\Modules\Analytics\Actions\TrackPostViewAction;
use App\Modules\Posts\Models\Post;
use Illuminate\Support\Facades\DB;

final class ShowPostAction
{
    public function __construct(
        private readonly TrackPostViewAction $trackPostView,
    ) {}

    public function execute(Post $post, ?User $viewer, string $ipAddress, ?string $userAgent): Post
    {
        $this->trackPostView->execute($post->id, $viewer, $ipAddress, $userAgent);

        $post->load('author')
            ->loadCount([
                'views as views_count',
                'views as unique_views_count' => fn ($query) => $query->select(DB::raw('COUNT(DISTINCT visitor_hash)')),
            ]);

        return $post;
    }
}

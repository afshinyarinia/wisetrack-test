<?php

namespace App\Modules\Posts\Actions;

use App\Models\User;
use App\Modules\Analytics\Actions\TrackPostViewAction;
use App\Modules\Posts\Models\Post;

final class ShowPostAction
{
    public function __construct(
        private readonly TrackPostViewAction $trackPostView,
    ) {}

    public function execute(Post $post, ?User $viewer, string $ipAddress, ?string $userAgent): Post
    {
        $post->load('author');

        $this->trackPostView->execute($post->id, $viewer, $ipAddress, $userAgent);

        return $post;
    }
}

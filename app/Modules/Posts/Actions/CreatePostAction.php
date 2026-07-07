<?php

namespace App\Modules\Posts\Actions;

use App\Modules\Posts\Data\CreatePostData;
use App\Modules\Posts\Models\Post;
use App\Modules\Shared\Storage\ImageStorageInterface;

final class CreatePostAction
{
    public function __construct(
        private readonly ImageStorageInterface $imageStorage,
    ) {}

    public function execute(CreatePostData $data): Post
    {
        return Post::query()->create([
            'user_id' => $data->userId,
            'title' => $data->title,
            'content' => $data->content,
            'image' => $data->image ? $this->imageStorage->storePostImage($data->image) : null,
        ])->load('author');
    }
}

<?php

namespace App\Modules\Posts\Data;

use App\Http\Requests\Posts\StorePostRequest;
use Illuminate\Http\UploadedFile;

final readonly class CreatePostData
{
    public function __construct(
        public int $userId,
        public string $title,
        public string $content,
        public ?UploadedFile $image,
    ) {}

    public static function fromRequest(StorePostRequest $request): self
    {
        return new self(
            userId: $request->user()->id,
            title: $request->string('title')->toString(),
            content: $request->string('content')->toString(),
            image: $request->file('image'),
        );
    }
}

<?php

namespace App\Modules\Posts\Data;

use App\Http\Requests\Posts\ListPostsRequest;

final readonly class ListPostsData
{
    public function __construct(
        public int $perPage,
    ) {}

    public static function fromRequest(ListPostsRequest $request): self
    {
        return new self(
            perPage: (int) $request->integer('per_page', 15),
        );
    }
}

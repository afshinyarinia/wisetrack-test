<?php

namespace App\Modules\Analytics\Data;

use Carbon\CarbonImmutable;

final readonly class TrackPostViewData
{
    public function __construct(
        public int $postId,
        public ?int $userId,
        public string $visitorHash,
        public string $ipAddress,
        public ?string $userAgent,
        public CarbonImmutable $viewedAt,
    ) {}

    public function viewedDate(): string
    {
        return $this->viewedAt->toDateString();
    }
}

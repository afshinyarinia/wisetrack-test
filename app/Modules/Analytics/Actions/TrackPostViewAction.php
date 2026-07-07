<?php

namespace App\Modules\Analytics\Actions;

use App\Models\User;
use App\Modules\Analytics\Data\TrackPostViewData;
use App\Modules\Analytics\Repositories\PostViewRepositoryInterface;
use App\Modules\Analytics\Services\VisitorIdentityResolver;
use Carbon\CarbonImmutable;

final class TrackPostViewAction
{
    public function __construct(
        private readonly VisitorIdentityResolver $visitorIdentityResolver,
        private readonly PostViewRepositoryInterface $postViewRepository,
    ) {}

    public function execute(int $postId, ?User $user, string $ipAddress, ?string $userAgent): void
    {
        $identity = $this->visitorIdentityResolver->resolve($user, $ipAddress, $userAgent);

        $this->postViewRepository->recordUniqueDailyView(new TrackPostViewData(
            postId: $postId,
            userId: $user?->id,
            visitorHash: $identity->hash,
            ipAddress: $ipAddress,
            userAgent: $userAgent,
            viewedAt: CarbonImmutable::now(),
        ));
    }
}

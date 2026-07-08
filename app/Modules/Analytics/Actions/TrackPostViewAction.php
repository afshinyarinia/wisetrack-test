<?php

namespace App\Modules\Analytics\Actions;

use App\Models\User;
use App\Modules\Analytics\Data\TrackPostViewData;
use App\Modules\Analytics\Repositories\PostViewRepositoryInterface;
use App\Modules\Analytics\Services\TopViewedPostsCache;
use App\Modules\Analytics\Services\VisitorIdentityResolver;
use Carbon\CarbonImmutable;

final class TrackPostViewAction
{
    public function __construct(
        private readonly VisitorIdentityResolver $visitorIdentityResolver,
        private readonly PostViewRepositoryInterface $postViewRepository,
        private readonly TopViewedPostsCache $topViewedPostsCache,
    ) {}

    public function execute(int $postId, ?User $user, string $ipAddress, ?string $userAgent): void
    {
        $identity = $this->visitorIdentityResolver->resolve($user, $ipAddress, $userAgent);

        $recorded = $this->postViewRepository->recordUniqueDailyView(new TrackPostViewData(
            postId: $postId,
            userId: $user?->id,
            visitorHash: $identity->hash,
            ipAddress: $ipAddress,
            userAgent: $userAgent,
            viewedAt: CarbonImmutable::now(),
        ));

        if ($recorded) {
            $this->topViewedPostsCache->invalidate();
        }
    }
}

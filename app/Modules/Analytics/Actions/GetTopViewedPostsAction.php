<?php

namespace App\Modules\Analytics\Actions;

use App\Models\User;
use App\Modules\Analytics\Repositories\PostAnalyticsRepositoryInterface;
use App\Modules\Analytics\Services\TopViewedPostsCache;
use App\Modules\Analytics\ValueObjects\DateRange;
use App\Modules\Posts\Models\Post;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

final class GetTopViewedPostsAction
{
    public function __construct(
        private readonly PostAnalyticsRepositoryInterface $analyticsRepository,
        private readonly TopViewedPostsCache $topViewedPostsCache,
    ) {}

    public function execute(DateRange $range, int $limit): Collection
    {
        $posts = Cache::remember(
            $this->topViewedPostsCache->key($range, $limit),
            $this->topViewedPostsCache->ttl(),
            fn (): array => $this->analyticsRepository
                ->topViewed($range, $limit)
                ->map(fn (Post $post): array => $this->toCacheRow($post))
                ->all(),
        );

        return collect($posts)->map(fn (array $row): Post => $this->fromCacheRow($row));
    }

    private function toCacheRow(Post $post): array
    {
        return [
            'attributes' => $post->getAttributes(),
            'author' => $post->author?->only(['id', 'name', 'email', 'created_at', 'updated_at']),
        ];
    }

    private function fromCacheRow(array $row): Post
    {
        $post = new Post();
        $post->setRawAttributes($row['attributes'], true);
        $post->exists = true;

        if ($row['author']) {
            $author = new User();
            $author->setRawAttributes($row['author'], true);
            $author->exists = true;
            $post->setRelation('author', $author);
        }

        return $post;
    }
}

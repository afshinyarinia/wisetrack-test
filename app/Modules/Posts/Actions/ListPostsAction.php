<?php

namespace App\Modules\Posts\Actions;

use App\Modules\Posts\Data\ListPostsData;
use App\Modules\Posts\Models\Post;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class ListPostsAction
{
    public function execute(ListPostsData $data): LengthAwarePaginator
    {
        return Post::query()
            ->with('author')
            ->withCount(['views as views_count', 'views as unique_views_count'])
            ->latest()
            ->paginate($data->perPage);
    }
}

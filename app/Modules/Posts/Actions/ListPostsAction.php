<?php

namespace App\Modules\Posts\Actions;

use App\Modules\Posts\Data\ListPostsData;
use App\Modules\Posts\Models\Post;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

final class ListPostsAction
{
    public function execute(ListPostsData $data): LengthAwarePaginator
    {
        return Post::query()
            ->with('author')
            ->withCount([
                'views as views_count',
                'views as unique_views_count' => fn ($query) => $query->select(DB::raw('COUNT(DISTINCT visitor_hash)')),
            ])
            ->latest()
            ->paginate($data->perPage);
    }
}

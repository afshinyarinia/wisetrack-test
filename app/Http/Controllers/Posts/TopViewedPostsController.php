<?php

namespace App\Http\Controllers\Posts;

use App\Http\Controllers\Controller;
use App\Http\Requests\Analytics\TopViewedPostsRequest;
use App\Http\Resources\TopViewedPostResource;
use App\Modules\Analytics\Actions\GetTopViewedPostsAction;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class TopViewedPostsController extends Controller
{
    public function __invoke(TopViewedPostsRequest $request, GetTopViewedPostsAction $action): AnonymousResourceCollection
    {
        return TopViewedPostResource::collection($action->execute(
            $request->dateRange(),
            (int) $request->integer('limit', 10),
        ));
    }
}

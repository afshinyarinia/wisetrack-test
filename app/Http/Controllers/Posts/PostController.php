<?php

namespace App\Http\Controllers\Posts;

use App\Http\Controllers\Controller;
use App\Http\Requests\Posts\ListPostsRequest;
use App\Http\Requests\Posts\StorePostRequest;
use App\Http\Resources\PostResource;
use App\Modules\Posts\Actions\CreatePostAction;
use App\Modules\Posts\Actions\ListPostsAction;
use App\Modules\Posts\Actions\ShowPostAction;
use App\Modules\Posts\Data\CreatePostData;
use App\Modules\Posts\Data\ListPostsData;
use App\Modules\Posts\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;

final class PostController extends Controller
{
    public function index(ListPostsRequest $request, ListPostsAction $listPosts): AnonymousResourceCollection
    {
        return PostResource::collection($listPosts->execute(ListPostsData::fromRequest($request)));
    }

    public function store(StorePostRequest $request, CreatePostAction $createPost): JsonResponse
    {
        return (new PostResource($createPost->execute(CreatePostData::fromRequest($request))))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Request $request, Post $post, ShowPostAction $showPost): PostResource
    {
        $viewer = Auth::guard('sanctum')->user();

        return new PostResource($showPost->execute(
            post: $post,
            viewer: $viewer,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        ));
    }
}

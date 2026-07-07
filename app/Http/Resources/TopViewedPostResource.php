<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TopViewedPostResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'author' => new UserResource($this->whenLoaded('author')),
            'total_views' => (int) $this->total_views,
            'unique_users' => (int) $this->unique_users,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}

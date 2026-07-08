<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TopViewedPostResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'rank' => (int) $this->rank,
            'post_id' => $this->id,
            'title' => $this->title,
            'author' => $this->author?->name,
            'total_views' => (int) $this->total_views,
            'unique_users' => (int) $this->unique_users,
            'trend' => $this->trend ?? 'stable',
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}

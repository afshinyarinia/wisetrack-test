<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostAnalyticsSummaryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'post_id' => $this->resource['post']->id,
            'title' => $this->resource['post']->title,
            'period' => $this->resource['period'],
            'analytics' => $this->resource['analytics'],
            'meta' => $this->resource['meta'],
        ];
    }
}

<?php

namespace App\Modules\Analytics\Models;

use App\Models\User;
use App\Modules\Posts\Models\Post;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['post_id', 'user_id', 'visitor_hash', 'ip_address', 'user_agent', 'viewed_date', 'viewed_at'])]
class PostView extends Model
{
    protected function casts(): array
    {
        return [
            'viewed_date' => 'date:Y-m-d',
            'viewed_at' => 'immutable_datetime',
        ];
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

<?php

namespace Database\Factories;

use App\Models\User;
use App\Modules\Posts\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Post>
 */
class PostFactory extends Factory
{
    protected $model = Post::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => \fake()->sentence(4),
            'content' => \fake()->paragraphs(3, true),
            'image' => null,
        ];
    }
}

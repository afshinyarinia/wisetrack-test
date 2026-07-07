<?php

namespace Database\Seeders;

use App\Models\User;
use App\Modules\Analytics\Models\PostView;
use App\Modules\Analytics\ValueObjects\VisitorIdentity;
use App\Modules\Posts\Models\Post;
use Carbon\CarbonImmutable;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $author = User::factory()->create([
            'name' => 'Demo Author',
            'email' => 'author@example.com',
            'password' => Hash::make('password123'),
        ]);

        $viewer = User::factory()->create([
            'name' => 'Demo Viewer',
            'email' => 'viewer@example.com',
            'password' => Hash::make('password123'),
        ]);

        $posts = Post::factory()
            ->count(5)
            ->for($author, 'author')
            ->create();

        foreach ($posts as $index => $post) {
            $this->seedViews($post, $viewer, $index + 1);
        }
    }

    private function seedViews(Post $post, User $viewer, int $days): void
    {
        for ($day = 0; $day < $days; $day++) {
            $viewedAt = CarbonImmutable::today()->subDays($day)->setTime(10, 0);
            $guestIp = '203.0.113.'.($post->id + $day);

            PostView::query()->insertOrIgnore([
                [
                    'post_id' => $post->id,
                    'user_id' => $viewer->id,
                    'visitor_hash' => VisitorIdentity::forUser($viewer->id)->hash,
                    'ip_address' => '198.51.100.10',
                    'user_agent' => 'Seeder Browser',
                    'viewed_date' => $viewedAt->toDateString(),
                    'viewed_at' => $viewedAt,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'post_id' => $post->id,
                    'user_id' => null,
                    'visitor_hash' => VisitorIdentity::forGuest($guestIp, 'Seeder Browser')->hash,
                    'ip_address' => $guestIp,
                    'user_agent' => 'Seeder Browser',
                    'viewed_date' => $viewedAt->toDateString(),
                    'viewed_at' => $viewedAt,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        }
    }
}

<?php

namespace Tests\Feature;

use App\Jobs\SendWelcomeEmailJob;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register(): void
    {
        Queue::fake();

        $response = $this->postJson('/api/register', [
            'name' => 'Afshin',
            'email' => 'afshin@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.user.name', 'Afshin')
            ->assertJsonPath('data.user.email', 'afshin@example.com')
            ->assertJsonStructure(['data' => ['user' => ['id', 'name', 'email'], 'token']]);

        $user = User::query()->where('email', 'afshin@example.com')->firstOrFail();

        $this->assertTrue(Hash::check('password123', $user->password));
        Queue::assertPushed(SendWelcomeEmailJob::class, fn (SendWelcomeEmailJob $job): bool => $job->userId === $user->id);
    }

    public function test_registration_fails_with_duplicate_email(): void
    {
        User::factory()->create(['email' => 'afshin@example.com']);

        $this->postJson('/api/register', [
            'name' => 'Afshin',
            'email' => 'afshin@example.com',
            'password' => 'password123',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email');
    }

    public function test_user_can_login_and_access_current_user(): void
    {
        $user = User::factory()->create([
            'email' => 'afshin@example.com',
            'password' => Hash::make('password123'),
        ]);

        $token = $this->postJson('/api/login', [
            'email' => 'afshin@example.com',
            'password' => 'password123',
        ])
            ->assertOk()
            ->assertJsonPath('data.user.id', $user->id)
            ->assertJsonStructure(['data' => ['token']])
            ->json('data.token');

        $this->withToken($token)
            ->getJson('/api/user')
            ->assertOk()
            ->assertJsonPath('data.email', 'afshin@example.com');
    }

    public function test_login_fails_with_wrong_credentials(): void
    {
        User::factory()->create([
            'email' => 'afshin@example.com',
            'password' => Hash::make('password123'),
        ]);

        $this->postJson('/api/login', [
            'email' => 'afshin@example.com',
            'password' => 'wrong-password',
        ])
            ->assertUnauthorized()
            ->assertJsonPath('message', 'Invalid credentials.');
    }
}

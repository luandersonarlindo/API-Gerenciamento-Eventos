<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_creates_user_and_returns_token(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Maria',
            'email' => 'maria@example.com',
            'password' => 'senha12345',
            'password_confirmation' => 'senha12345',
        ]);

        $response->assertCreated()
            ->assertJsonStructure(['message', 'user' => ['id', 'name', 'email'], 'token'])
            ->assertJsonMissingPath('user.password');

        $this->assertDatabaseHas('users', ['email' => 'maria@example.com']);
    }

    public function test_register_validates_input(): void
    {
        User::factory()->create(['email' => 'maria@example.com']);

        $this->postJson('/api/register', ['email' => 'maria@example.com', 'password' => '123'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    public function test_login_returns_token_and_rejects_wrong_password(): void
    {
        User::factory()->create(['email' => 'maria@example.com', 'password' => 'senha12345']);

        $this->postJson('/api/login', ['email' => 'maria@example.com', 'password' => 'errada'])
            ->assertUnauthorized();

        $this->postJson('/api/login', ['email' => 'maria@example.com', 'password' => 'senha12345'])
            ->assertOk()
            ->assertJsonStructure(['user', 'token']);
    }

    public function test_me_and_logout_with_real_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('api')->plainTextToken;

        $this->withToken($token)->getJson('/api/me')
            ->assertOk()
            ->assertJsonPath('data.email', $user->email);

        $this->withToken($token)->postJson('/api/logout')->assertOk();

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_protected_routes_require_authentication(): void
    {
        $this->getJson('/api/me')->assertUnauthorized();
        $this->getJson('/api/events')->assertUnauthorized();
        $this->postJson('/api/events', ['name' => 'X'])->assertUnauthorized();
        $this->getJson('/api/registrations')->assertUnauthorized();
        $this->postJson('/api/events/1/registrations')->assertUnauthorized();
    }
}

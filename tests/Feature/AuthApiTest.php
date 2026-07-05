<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Saint Pavlusha',
            'email' => 'test1@test.ru',
            'password' => 'test123456',
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => ['token'],
        ]);
    }

    public function test_user_cannot_register_with_existing_email(): void
    {
        User::factory()->create(['email' => 'test1@test.ru']);

        $response = $this->postJson('/api/register', [
            'name' => 'Saint Pavlusha',
            'email' => 'test1@test.ru',
            'password' => 'test123456',
        ]);

        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'errors' => ['email']]);
    }

    public function test_user_can_login(): void
    {
        User::factory()->create([
            'email' => 'test1@test.ru',
            'password' => 'test123456',
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test1@test.ru',
            'password' => 'test123456',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => ['token'],
        ]);
    }

    public function test_user_cannot_login_with_wrong_password(): void
    {
        User::factory()->create([
            'email' => 'test1@test.ru',
            'password' => 'test123456',
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test1@test.ru',
            'password' => 'wrongPass',
        ]);

        $response->assertStatus(401);
        $response->assertJsonStructure(['message']);
    }

    public function test_user_cannot_login_with_wrong_email(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => 'nonexistent@test.ru',
            'password' => 'test123456',
        ]);

        $response->assertStatus(401);
        $response->assertJsonStructure(['message']);
    }

    public function test_user_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/logout');

        $response->assertStatus(200);
    }

    public function test_guest_cannot_logout(): void
    {
        $response = $this->postJson('/api/logout');

        $response->assertStatus(401);
        $response->assertJsonStructure(['message']);
    }

    public function test_tokens_deleted_after_logout(): void
    {
        $user = User::factory()->create();
        $user->createToken('auth-token');

        $this->assertEquals(1, $user->tokens()->count());

        $this->actingAs($user)->postJson('/api/logout');

        $this->assertEquals(0, $user->fresh()->tokens()->count());
    }
}

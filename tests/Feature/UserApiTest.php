<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UserApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_get_profile(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/api/user');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => ['id', 'name', 'email'],
        ]);
    }

    public function test_guest_cannot_get_profile(): void
    {
        $response = $this->getJson('/api/user');

        $response->assertStatus(401);
        $response->assertJsonStructure(['message']);
    }

    public function test_authenticated_user_can_update_profile(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->patchJson('/api/user', [
                'name' => 'John Snow',
                'email' => $user->email,
            ]);

        $response->assertStatus(200);
    }

    public function test_user_can_update_avatar(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $file = UploadedFile::fake()->image('avatar.jpg');

        $response = $this->actingAs($user)
            ->patchJson('/api/user', [
                'name' => $user->name,
                'email' => $user->email,
                'file' => $file,
            ]);

        $response->assertStatus(200);
    }

    public function test_user_cannot_update_profile_with_existing_email(): void
    {
        $user = User::factory()->create();
        $newUser = User::factory()->create(['email' => 'user2@test.ru']);

        $response = $this->actingAs($user)
            ->patchJson('/api/user', [
                'name' => 'New User',
                'email' => $newUser->email,
            ]);

        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'errors' => ['email']]);
    }

    public function test_user_can_update_password(): void
    {
        $user = User::factory()->create(['password' => 'oldpassword123']);

        $response = $this->actingAs($user)
            ->patchJson('/api/user', [
                'name' => $user->name,
                'email' => $user->email,
                'password' => 'newpassword123',
            ]);

        $response->assertStatus(200);
        $this->assertTrue(
            Hash::check('newpassword123', $user->fresh()->password)
        );
    }

    public function test_old_avatar_is_deleted_when_new_one_uploaded(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('avatars/old.jpg', 'old');

        $user = User::factory()->create(['avatar' => 'avatars/old.jpg']);

        $this->actingAs($user)
            ->patchJson('/api/user', [
                'name' => $user->name,
                'email' => $user->email,
                'file' => UploadedFile::fake()->image('new.jpg'),
            ]);

        Storage::disk('public')->assertMissing('avatars/old.jpg');
    }
}

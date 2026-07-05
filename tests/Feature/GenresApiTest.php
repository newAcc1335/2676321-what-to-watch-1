<?php

namespace Tests\Feature;

use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenresApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_anyone_can_get_genres_list(): void
    {
        Genre::factory()->count(3)->create();

        $response = $this->getJson('/api/genres');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'name'],
            ],
        ]);
    }

    public function test_moderator_can_update_genre(): void
    {
        $moderator = User::factory()->create(['role' => 'moderator']);
        $genre = Genre::factory()->create();

        $response = $this->actingAs($moderator)
            ->patchJson("/api/genres/{$genre->id}", ['name' => 'New Name']);

        $response->assertStatus(200);
    }

    public function test_user_cannot_update_genre(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $response = $this->actingAs($user)
            ->patchJson("/api/genres/{$genre->id}", ['name' => 'New Name']);

        $response->assertStatus(403);
        $response->assertJsonStructure(['message']);
    }
}

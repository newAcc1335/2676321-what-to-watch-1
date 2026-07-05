<?php

namespace Tests\Feature;

use App\Models\Film;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Genre;

class FilmsApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_anyone_can_get_films_list(): void
    {
        Film::factory()->count(3)->create();

        $response = $this->getJson('/api/films');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'data' => [
                    '*' => ['id', 'name', 'preview_image'],
                ],
            ],
        ]);
    }

    public function test_moderator_can_add_film(): void
    {
        $moderator = User::factory()->create(['role' => 'moderator']);

        $response = $this->actingAs($moderator)
            ->postJson('/api/films', ['imdb_id' => 'tt1234567']);

        $response->assertStatus(201);
    }

    public function test_user_cannot_add_film(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/api/films', ['imdb_id' => 'tt1234567']);

        $response->assertStatus(403);
        $response->assertJsonStructure(['message']);
    }

    public function test_anyone_can_get_film(): void
    {
        $film = Film::factory()->create();

        $response = $this->getJson("/api/films/{$film->id}");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => ['id', 'name', 'imdb_id', 'status']
        ]);
    }

    public function test_returns_404_for_nonexistent_film(): void
    {
        $response = $this->getJson('/api/films/999');

        $response->assertStatus(404);
        $response->assertJsonStructure(['message']);
    }

    public function test_authenticated_user_gets_is_favorite_field(): void
    {
        $film = Film::factory()->create();
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->getJson("/api/films/{$film->id}");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => ['id', 'name', 'is_favorite']
        ]);
    }

    public function test_guest_does_not_get_is_favorite_field(): void
    {
        $film = Film::factory()->create();

        $response = $this->getJson("/api/films/{$film->id}");

        $response->assertStatus(200);
        $response->assertJsonMissing(['is_favorite']);
    }

    public function test_anyone_can_get_similar_films(): void
    {
        $film = Film::factory()
            ->hasAttached(Genre::factory()->count(2))
            ->create();

        Film::factory()
            ->hasAttached($film->genres)
            ->count(3)
            ->create();

        $response = $this->getJson("/api/films/{$film->id}/similar");

        $response->assertStatus(200);
    }

    public function test_moderator_can_update_film(): void
    {
        $film = Film::factory()->create();
        $moderator = User::factory()->create(['role' => 'moderator']);

        $response = $this->actingAs($moderator)
            ->patchJson("/api/films/{$film->id}", [
                'name' => 'New Name',
                'status' => 'ready',
            ]);

        $response->assertStatus(200);
    }

    public function test_user_cannot_update_film(): void
    {
        $film = Film::factory()->create();
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->patchJson("/api/films/{$film->id}", ['name' => 'New Name']);

        $response->assertStatus(403);
        $response->assertJsonStructure(['message']);
    }
}

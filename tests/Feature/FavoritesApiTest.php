<?php

namespace Tests\Feature;

use App\Models\Film;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FavoritesApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_get_favorites(): void
    {
        $film = Film::factory()->create();
        $user = User::factory()->create();
        $user->favoriteFilms()->attach($film->id);

        $response = $this->actingAs($user)->getJson('/api/favorite');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [['id', 'name']]
        ]);
    }

    public function test_guest_cannot_get_favorites(): void
    {
        $response = $this->getJson('/api/favorite');

        $response->assertStatus(401);
    }

    public function test_user_can_add_film_to_favorites(): void
    {
        $film = Film::factory()->create();
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson("/api/films/{$film->id}/favorite");

        $response->assertStatus(200);
        $this->assertDatabaseHas('favorites', [
            'user_id' => $user->id,
            'film_id' => $film->id,
        ]);
    }

    public function test_user_cannot_add_film_to_favorites_twice(): void
    {
        $film = Film::factory()->create();
        $user = User::factory()->create();
        $user->favoriteFilms()->attach($film->id);

        $response = $this->actingAs($user)->postJson("/api/films/{$film->id}/favorite");

        $response->assertStatus(422);
    }

    public function test_user_can_remove_film_from_favorites(): void
    {
        $film = Film::factory()->create();
        $user = User::factory()->create();
        $user->favoriteFilms()->attach($film->id);

        $response = $this->actingAs($user)->deleteJson("/api/films/{$film->id}/favorite");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('favorites', [
            'user_id' => $user->id,
            'film_id' => $film->id,
        ]);
    }

    public function test_user_cannot_remove_film_not_in_favorites(): void
    {
        $film = Film::factory()->create();
        $user = User::factory()->create();

        $response = $this->actingAs($user)->deleteJson("/api/films/{$film->id}/favorite");

        $response->assertStatus(422);
    }

    public function test_guest_cannot_add_to_favorites(): void
    {
        $film = Film::factory()->create();

        $response = $this->postJson("/api/films/{$film->id}/favorite");

        $response->assertStatus(401);
    }
}

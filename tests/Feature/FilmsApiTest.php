<?php

namespace Tests\Feature;

use App\Jobs\UpdateFilmJob;
use App\Models\Film;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class FilmsApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_anyone_can_get_films_list(): void
    {
        Film::factory()->count(10)->create();

        $response = $this->getJson('/api/films');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'name', 'preview_image'],
            ],
            'current_page',
            'first_page_url',
            'last_page_url',
            'next_page_url',
            'prev_page_url',
            'per_page',
            'total',
        ]);
        $response->assertJsonCount(8, 'data');
        $response->assertJsonPath('total', 10);
    }

    public function test_moderator_can_add_film(): void
    {
        Queue::fake();

        $moderator = User::factory()->create(['role' => 'moderator']);

        $response = $this->actingAs($moderator)
            ->postJson('/api/films', ['imdb_id' => 'tt1234567']);

        $response->assertStatus(201);
        $this->assertDatabaseHas('films', ['imdb_id' => 'tt1234567', 'status' => 'pending']);
        Queue::assertPushed(UpdateFilmJob::class);
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
            'data' => ['id', 'name', 'imdb_id', 'status'],
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
            'data' => ['id', 'name', 'is_favorite'],
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
                'imdb_id' => $film->imdb_id,
                'status' => 'ready',
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('films', ['id' => $film->id, 'name' => 'New Name']);
    }

    public function test_film_update_ignores_protected_fields(): void
    {
        $film = Film::factory()->create(['rating' => null, 'scores_count' => 0]);
        $moderator = User::factory()->create(['role' => 'moderator']);

        $this->actingAs($moderator)
            ->patchJson("/api/films/{$film->id}", [
                'name' => 'New Name',
                'imdb_id' => $film->imdb_id,
                'status' => 'ready',
                'rating' => 10,
                'scores_count' => 9999,
                'is_promo' => true,
            ]);

        $this->assertDatabaseHas('films', [
            'id' => $film->id,
            'scores_count' => 0,
            'is_promo' => false,
        ]);
    }

    public function test_films_list_rejects_invalid_order_by(): void
    {
        $response = $this->getJson('/api/films?order_by=foobar');

        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'errors' => ['order_by']]);
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

    public function test_similar_films_returns_max_4(): void
    {
        $genre = Genre::factory()->create();
        $film = Film::factory()->hasAttached($genre)->create(['status' => 'ready']);
        Film::factory()->hasAttached($genre)->count(10)->create(['status' => 'ready']);

        $response = $this->getJson("/api/films/{$film->id}/similar");

        $response->assertStatus(200);
        $this->assertLessThanOrEqual(4, count($response->json('data')));
    }

    public function test_similar_films_returns_404_for_nonexistent(): void
    {
        $response = $this->getJson('/api/films/999999/similar');

        $response->assertStatus(404);
        $response->assertJsonStructure(['message']);
    }

    public function test_similar_films_only_same_genre(): void
    {
        $genre = Genre::factory()->create();
        $otherGenre = Genre::factory()->create();

        $film = Film::factory()->hasAttached($genre)->create(['status' => 'ready']);
        Film::factory()->hasAttached($genre)->count(3)->create(['status' => 'ready']);
        $otherFilm = Film::factory()->hasAttached($otherGenre)->create(['status' => 'ready']);

        $response = $this->getJson("/api/films/{$film->id}/similar");

        $ids = collect($response->json('data'))->pluck('id');
        $this->assertNotContains($otherFilm->id, $ids);
    }

    public function test_moderator_can_filter_films_by_status(): void
    {
        Film::factory()->create(['status' => 'pending']);
        Film::factory()->count(2)->create(['status' => 'ready']);
        $moderator = User::factory()->create(['role' => 'moderator']);

        $response = $this->actingAs($moderator)->getJson('/api/films?status=pending');

        $response->assertStatus(200);
        $response->assertJsonPath('total', 1);
    }

    public function test_regular_user_cannot_filter_films_by_status(): void
    {
        Film::factory()->create(['status' => 'pending']);
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/api/films?status=pending');

        $response->assertStatus(200);
        $response->assertJsonPath('total', 0);
    }

    public function test_films_list_cache_is_invalidated_after_update(): void
    {
        $film = Film::factory()->create(['name' => 'Old Name', 'status' => 'ready']);
        $moderator = User::factory()->create(['role' => 'moderator']);

        $this->getJson('/api/films')->assertJsonPath('data.0.name', 'Old Name');

        $this->actingAs($moderator)->patchJson("/api/films/{$film->id}", [
            'name' => 'New Name',
            'imdb_id' => $film->imdb_id,
            'status' => 'ready',
        ]);

        $this->getJson('/api/films')->assertJsonPath('data.0.name', 'New Name');
    }
}

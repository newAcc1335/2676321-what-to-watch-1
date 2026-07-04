<?php

namespace Tests\Feature;

use App\Models\Film;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

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
                '*' => ['id', 'name', 'preview_image'],
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
    }
}

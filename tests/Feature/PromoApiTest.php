<?php

namespace Tests\Feature;

use App\Models\Film;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PromoApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_anyone_can_get_promo_film(): void
    {
        Film::factory()->create(['is_promo' => true]);

        $response = $this->getJson('/api/promo');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => ['id', 'name', 'is_promo']
        ]);
    }

    public function test_returns_404_when_no_promo_film(): void
    {
        $response = $this->getJson('/api/promo');

        $response->assertStatus(404);
        $response->assertJsonStructure(['message']);
    }

    public function test_moderator_can_set_promo_film(): void
    {
        $film = Film::factory()->create();
        $moderator = User::factory()->create(['role' => 'moderator']);

        $response = $this->actingAs($moderator)->postJson("/api/promo/{$film->id}");

        $response->assertStatus(200);
        $this->assertDatabaseHas('films', [
            'id' => $film->id,
            'is_promo' => true,
        ]);
    }

    public function test_user_cannot_set_promo_film(): void
    {
        $film = Film::factory()->create();
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson("/api/promo/{$film->id}");

        $response->assertStatus(403);
        $response->assertJsonStructure(['message']);
    }

    public function test_only_one_film_can_be_promo(): void
    {
        $moderator = User::factory()->create(['role' => 'moderator']);
        $film1 = Film::factory()->create(['is_promo' => true]);
        $film2 = Film::factory()->create();

        $response = $this->actingAs($moderator)->postJson("/api/promo/{$film2->id}");
        $response->assertStatus(200);

        $this->assertDatabaseHas('films', ['id' => $film1->id, 'is_promo' => false]);
        $this->assertDatabaseHas('films', ['id' => $film2->id, 'is_promo' => true]);
    }
}

<?php

namespace Tests\Unit;

use App\Models\Comment;
use App\Models\Film;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FilmModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_film_rating_is_calculated_correctly(): void
    {
        $film = Film::factory()->create();

        Comment::factory()->create(['film_id' => $film->id, 'rating' => 1]);
        Comment::factory()->create(['film_id' => $film->id, 'rating' => 2]);
        Comment::factory()->create(['film_id' => $film->id, 'rating' => 3]);
        Comment::factory()->create(['film_id' => $film->id, 'rating' => 4]);

        $film->updateRating();

        $this->assertEquals(4, $film->scores_count);
        $this->assertEquals(2.5, $film->rating);
    }
}

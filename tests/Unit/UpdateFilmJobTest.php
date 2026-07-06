<?php

namespace Tests\Unit;

use App\Contracts\MovieRepositoryInterface;
use App\Jobs\UpdateFilmJob;
use App\Models\Film;
use App\Services\GenreService;
use App\Services\MovieService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Mockery\MockInterface;
use Tests\TestCase;

class UpdateFilmJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_updates_film_data(): void
    {
        $film = Film::factory()->create([
            'imdb_id' => 'tt1109624',
            'status' => 'pending',
        ]);

        $movieData = [
            'Response' => 'True',
            'Title' => 'The Proposal',
            'Plot' => 'A pushy boss forces her young assistant to marry her in order to keep her visa status.',
            'Director' => 'Anne Fletcher',
            'Actors' => 'Sandra Bullock, Ryan Reynolds',
            'Year' => '2009',
            'Runtime' => '108 min',
            'Genre' => 'Comedy, Romance',
            'Poster' => 'https://example.com/poster.jpg',
        ];

        $this->mock(MovieRepositoryInterface::class, function (MockInterface $mock) use ($movieData) {
            $mock->shouldReceive('findByImdbId')
                ->with('tt1109624')
                ->andReturn($movieData);
        });

        (new UpdateFilmJob('tt1109624'))->handle(app(MovieService::class), app(GenreService::class));
        $film->refresh();
        $this->assertEquals('The Proposal', $film->name);
        $this->assertEquals(2009, $film->released);
        $this->assertEquals('moderate', $film->status);
        $this->assertEquals('https://example.com/poster.jpg', $film->poster_image);
        $this->assertDatabaseHas('genres', ['name' => 'Comedy']);
        $this->assertDatabaseHas('genres', ['name' => 'Romance']);
        $this->assertCount(2, $film->genres);
    }

    public function test_job_is_dispatched_to_queue(): void
    {
        Queue::fake();

        Film::factory()->create(['imdb_id' => 'tt1109624']);

        UpdateFilmJob::dispatch('tt1109624');

        Queue::assertPushed(UpdateFilmJob::class);
    }
}

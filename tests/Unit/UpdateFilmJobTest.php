<?php

namespace Tests\Unit;

use App\Jobs\UpdateFilmJob;
use App\Models\Film;
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
        ];

        $this->mock(MovieService::class, function (MockInterface $mock) use ($movieData) {
            $mock->shouldReceive('getMovieInfo')
                ->with('tt1109624')
                ->andReturn($movieData);
        });

        (new UpdateFilmJob('tt1109624'))->handle(app(MovieService::class));

        $film->refresh();
        $this->assertEquals('The Proposal', $film->name);
        $this->assertEquals(2009, $film->released);
        $this->assertEquals('moderate', $film->status);
    }

    public function test_job_is_dispatched_to_queue(): void
    {
        Queue::fake();

        Film::factory()->create(['imdb_id' => 'tt1109624']);

        UpdateFilmJob::dispatch('tt1109624');

        Queue::assertPushed(UpdateFilmJob::class);
    }
}

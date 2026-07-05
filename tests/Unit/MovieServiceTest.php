<?php

namespace Tests\Unit;

use App\Contracts\MovieRepositoryInterface;
use App\Services\MovieService;
use Mockery\MockInterface;
use Tests\TestCase;

class MovieServiceTest extends TestCase
{
    public function test_get_movie_info_returns_data(): void
    {
        $movieData = [
            'Title' => 'American Pie',
            'Year' => '1999',
            'Response' => 'True',
        ];

        $this->mock(MovieRepositoryInterface::class, function (MockInterface $mock) use ($movieData) {
            $mock->shouldReceive('findByImdbId')
                ->with('tt0179007')
                ->andReturn($movieData);
        });

        $service = app(MovieService::class);
        $result = $service->getMovieInfo('tt0179007');

        $this->assertEquals($movieData, $result);
    }

    public function test_get_movie_info_returns_null_when_not_found(): void
    {
        $this->mock(MovieRepositoryInterface::class, function (MockInterface $mock) {
            $mock->shouldReceive('findByImdbId')
                ->with('tt0000000')
                ->andReturn(null);
        });

        $service = app(MovieService::class);
        $result = $service->getMovieInfo('tt0000000');

        $this->assertNull($result);
    }
}

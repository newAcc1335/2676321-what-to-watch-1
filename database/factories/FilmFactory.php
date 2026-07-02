<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class FilmFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(),
            'director' => [$this->faker->name()],
            'starring' => [$this->faker->name(), $this->faker->name()],
            'released' => $this->faker->year(),
            'run_time' => $this->faker->numberBetween(20, 360),
            'status' => 'ready',
            'is_promo' => false,
            'rating' => null,
            'scores_count' => 0,
            'imdb_id' => 'tt'.$this->faker->unique()->numerify('#######'),
        ];
    }
}

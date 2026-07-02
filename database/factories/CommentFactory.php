<?php

namespace Database\Factories;

use App\Models\Comment;
use App\Models\Film;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Comment>
 */
class CommentFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'film_id' => Film::factory(),
            'text' => $this->faker->paragraphs(3, true),
            'rating' => $this->faker->numberBetween(1, 10),
            'comment_id' => null,
        ];
    }
}

<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Film;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentsApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_anyone_can_get_comments(): void
    {
        $film = Film::factory()->create();
        Comment::factory()->count(3)->create(['film_id' => $film->id]);

        $response = $this->getJson("/api/comments/{$film->id}");

        $response->assertStatus(200);
    }

    public function test_authenticated_user_can_add_comment(): void
    {
        $user = User::factory()->create();
        $film = Film::factory()->create();

        $response = $this->actingAs($user)
            ->postJson("/api/comments/{$film->id}", [
                'text' => 'Очень длинный текст комментария для проверки минимальной длины в пятьдесят символов',
                'rating' => 8,
            ]);

        $response->assertStatus(201);
    }

    public function test_guest_cannot_add_comment(): void
    {
        $film = Film::factory()->create();

        $response = $this->postJson("/api/comments/{$film->id}", [
            'text' => 'Очень длинный текст комментария для проверки минимальной длины в пятьдесят символов',
            'rating' => 8,
        ]);

        $response->assertStatus(401);
    }

    public function test_user_cannot_delete_others_comment(): void
    {
        $user = User::factory()->create();
        $comment = Comment::factory()->create();

        $response = $this->actingAs($user)
            ->deleteJson("/api/comments/{$comment->id}");

        $response->assertStatus(403);
    }
}

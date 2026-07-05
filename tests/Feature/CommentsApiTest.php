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

    public function test_comment_text_must_be_at_least_50_characters(): void
    {
        $film = Film::factory()->create();
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson("/api/comments/{$film->id}", [
                'text' => 'Короткий текст',
                'rating' => 8,
            ]);

        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'errors' => ['text']]);
    }

    public function test_guest_cannot_add_comment(): void
    {
        $film = Film::factory()->create();

        $response = $this->postJson("/api/comments/{$film->id}", [
            'text' => 'Главный герой напомнил мне моего кота — такой же непредсказуемый и ест попкорн лапами',
            'rating' => 8,
        ]);

        $response->assertStatus(401);
        $response->assertJsonStructure(['message']);
    }

    public function test_user_cannot_delete_others_comment(): void
    {
        $user = User::factory()->create();
        $comment = Comment::factory()->create();

        $response = $this->actingAs($user)
            ->deleteJson("/api/comments/{$comment->id}");

        $response->assertStatus(403);
        $response->assertJsonStructure(['message']);
    }

    public function test_user_can_update_own_comment(): void
    {
        $user = User::factory()->create();
        $comment = Comment::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)
            ->patchJson("/api/comments/{$comment->id}", [
                'text' => 'Фильм настолько хорош, что я решил написать отзыв вместо того чтобы прыгнуть с балкона',
                'rating' => 5,
            ]);

        $response->assertStatus(200);
    }

    public function test_user_cannot_update_others_comment(): void
    {
        $user = User::factory()->create();
        $comment = Comment::factory()->create();

        $response = $this->actingAs($user)
            ->patchJson("/api/comments/{$comment->id}", [
                'text' => 'Фильм настолько хорош, что я решил написать отзыв вместо того чтобы прыгнуть с балкона',
                'rating' => 5,
            ]);

        $response->assertStatus(403);
        $response->assertJsonStructure(['message']);
    }

    public function test_user_can_delete_own_comment(): void
    {
        $user = User::factory()->create();
        $comment = Comment::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)
            ->deleteJson("/api/comments/{$comment->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
    }

    public function test_moderator_can_delete_any_comment(): void
    {
        $moderator = User::factory()->create(['role' => 'moderator']);
        $comment = Comment::factory()->create();

        $response = $this->actingAs($moderator)
            ->deleteJson("/api/comments/{$comment->id}");

        $response->assertStatus(200);
    }
}

<?php

namespace Tests\Unit;

use App\Models\Comment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_comment_returns_author_name(): void
    {
        $user = User::factory()->create(['name' => 'Saint Pavlusha']);
        $comment = Comment::factory()->create(['user_id' => $user->id]);

        $authorName = $comment->author_name;

        $this->assertEquals('Saint Pavlusha', $authorName);
    }

    public function test_anonymous_comment_returns_guest(): void
    {
        $comment = Comment::factory()->create(['user_id' => null]);
        $authorName = $comment->author_name;

        $this->assertEquals('Гость', $authorName);
    }
}

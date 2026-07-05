<?php

namespace App\Http\Controllers;

use App\Http\Responses\SuccessResponse;
use App\Models\Comment;
use App\Models\Film;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CommentController extends Controller
{
    /**
     * Получение списка комментариев к данному фильму.
     *
     * Endpoint: GET /api/comments/{id}
     */
    public function index(int $filmId): SuccessResponse
    {
        $film = Film::findOrFail($filmId);

        $comments = $film->comments()
            ->with('user')
            ->latest()
            ->get()
            ->map(function (Comment $comment) {
                return [
                    'id' => $comment->id,
                    'text' => $comment->text,
                    'author_name' => $comment->author_name,
                    'rating' => $comment->rating,
                    'created_at' => $comment->created_at,
                ];
            });

        return new SuccessResponse($comments);
    }

    /**
     * Добавление комментария к данному фильму.
     *
     * Endpoint: POST /api/comments/{id}
     */
    public function store(Request $request, int $filmId): SuccessResponse
    {
        $film = Film::findOrFail($filmId);

        $request->validate([
            'text' => 'required|string|min:50|max:400',
            'rating' => 'required|integer|min:1|max:10',
            'comment_id' => 'nullable|exists:comments,id',
        ]);

        $comment = $film->comments()->create([
            'user_id' => $request->user()->id,
            'text' => $request->text,
            'rating' => $request->rating,
            'comment_id' => $request->comment_id,
        ]);

        $film->updateRating();

        return new SuccessResponse($comment, 201);
    }

    /**
     * Редактирование комментария.
     *
     * Endpoint: PATCH /api/comments/{comment}
     */
    public function update(Request $request, Comment $comment): SuccessResponse
    {
        Gate::authorize('update-comment', $comment);

        $request->validate([
            'text' => 'required|string|min:50|max:400',
            'rating' => 'nullable|integer|min:1|max:10',
        ]);

        $comment->update($request->only(['text', 'rating']));
        $comment->film->updateRating();

        return new SuccessResponse($comment);
    }

    /**
     * Удаление комментария.
     *
     * Endpoint: DELETE /api/comments/{comment}
     */
    public function destroy(Comment $comment): SuccessResponse
    {
        Gate::authorize('destroy-comment', $comment);

        $film = $comment->film;
        $comment->delete();
        $film->updateRating();

        return new SuccessResponse();
    }
}

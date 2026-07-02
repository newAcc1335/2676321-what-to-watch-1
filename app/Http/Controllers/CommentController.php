<?php

namespace App\Http\Controllers;

use App\Http\Responses\SuccessResponse;
use App\Models\Comment;
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
        return new SuccessResponse;
    }

    /**
     * Добавление комментария к данному фильму.
     *
     * Endpoint: POST /api/comments/{id}
     */
    public function store(Request $request, int $filmId): SuccessResponse
    {
        return new SuccessResponse;
    }

    /**
     * Редактирование комментария.
     *
     * Endpoint: PATCH /api/comments/{comment}
     */
    public function update(Request $request, Comment $comment): SuccessResponse
    {
        Gate::authorize('update-comment', $comment);

        return new SuccessResponse;
    }

    /**
     * Удаление комментария.
     *
     * Endpoint: DELETE /api/comments/{comment}
     */
    public function destroy(Comment $comment): SuccessResponse
    {
        Gate::authorize('destroy-comment', $comment);

        return new SuccessResponse;
    }
}

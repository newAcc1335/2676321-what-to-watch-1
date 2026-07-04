<?php

namespace App\Http\Controllers;

use App\Http\Responses\SuccessResponse;
use Illuminate\Http\Request;
use App\Models\Film;

class FavoriteController extends Controller
{
    /**
     * Получение списка избранных фильмов пользователя.
     *
     * Endpoint: GET /api/favorite
     */
    public function index(Request $request): SuccessResponse
    {
        $films = $request->user()->favoriteFilms()->latest('favorites.created_at')->get();

        return new SuccessResponse($films);
    }

    /**
     * Добавление фильма в избранное.
     *
     * Endpoint: POST /api/films/{id}/favorite
     */
    public function store(Request $request, int $filmId): SuccessResponse
    {
        $film = Film::findOrFail($filmId);

        if ($request->user()->favoriteFilms()->where('film_id', $filmId)->exists()) {
            abort(422, 'Фильм уже находится в избранном');
        }

        $request->user()->favoriteFilms()->attach($filmId);

        return new SuccessResponse($film);
    }

    /**
     * Удаление фильма из избранного.
     *
     * Endpoint: DELETE /api/films/{id}/favorite
     */
    public function destroy(Request $request, int $filmId): SuccessResponse
    {
        $film = Film::findOrFail($filmId);

        if (!$request->user()->favoriteFilms()->where('film_id', $filmId)->exists()) {
            abort(422, 'Фильм не находится в избранном');
        }

        $request->user()->favoriteFilms()->detach($filmId);

        return new SuccessResponse($film);
    }
}

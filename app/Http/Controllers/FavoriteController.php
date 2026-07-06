<?php

namespace App\Http\Controllers;

use App\Http\Responses\SuccessResponse;
use App\Models\Film;
use Illuminate\Http\Request;

final class FavoriteController extends Controller
{
    /**
     * Получение списка избранных фильмов пользователя.
     *
     * Endpoint: GET /api/favorite
     */
    public function index(Request $request): SuccessResponse
    {
        $films = $request->user()->favoriteFilms()->latest('favorites.created_at')->paginate(8);

        return new SuccessResponse($films);
    }

    /**
     * Добавление фильма в избранное.
     *
     * Endpoint: POST /api/films/{film}/favorite
     */
    public function store(Request $request, Film $film): SuccessResponse
    {
        if ($request->user()->favoriteFilms()->where('film_id', $film->id)->exists()) {
            abort(422, 'Фильм уже находится в избранном');
        }

        $request->user()->favoriteFilms()->attach($film->id);

        return new SuccessResponse($film);
    }

    /**
     * Удаление фильма из избранного.
     *
     * Endpoint: DELETE /api/films/{film}/favorite
     */
    public function destroy(Request $request, Film $film): SuccessResponse
    {
        if (! $request->user()->favoriteFilms()->where('film_id', $film->id)->exists()) {
            abort(422, 'Фильм не находится в избранном');
        }

        $request->user()->favoriteFilms()->detach($film->id);

        return new SuccessResponse($film);
    }
}

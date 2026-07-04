<?php

namespace App\Http\Controllers;

use App\Http\Responses\SuccessResponse;
use App\Jobs\UpdateFilmJob;
use App\Models\Film;
use Illuminate\Http\Request;

class FilmController extends Controller
{
    /**
     * Получение списка фильмов с пагинацией и фильтрацией.
     *
     * Endpoint: GET /api/films
     */
    public function index(Request $request): SuccessResponse
    {
        return new SuccessResponse;
    }

    /**
     * Получение информации о данном фильме.
     *
     * Endpoint: GET /api/films/{id}
     */
    public function show(int $filmId): SuccessResponse
    {
        return new SuccessResponse;
    }

    /**
     * Добавление нового фильма в базу (только для модератора).
     *
     * Endpoint: POST /api/films
     */
    public function store(Request $request): SuccessResponse
    {
        $request->validate([
            'imdb_id' => 'required|unique:films,imdb_id|regex:/^tt\d+$/',
        ]);

        $film = Film::create([
            'imdb_id' => $request->imdb_id,
            'status' => 'pending',
        ]);

        UpdateFilmJob::dispatch($film->imdb_id);

        return new SuccessResponse($film, 201);
    }

    /**
     * Редактирование фильма.
     *
     * Endpoint: PATCH /api/films/{id}
     */
    public function update(Request $request, int $filmId): SuccessResponse
    {
        return new SuccessResponse;
    }

    /**
     * Получение списка похожих фильмов.
     *
     * Endpoint: GET /api/films/{id}/similar
     */
    public function similar(int $filmId): SuccessResponse
    {
        return new SuccessResponse;
    }
}

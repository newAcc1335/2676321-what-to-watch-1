<?php

namespace App\Http\Controllers;

use App\Http\Responses\SuccessResponse;
use App\Jobs\UpdateFilmJob;
use App\Models\Film;
use App\Services\GenreService;
use Illuminate\Http\Request;

class FilmController extends Controller
{
    public function __construct(private GenreService $genreService)
    {
    }

    /**
     * Получение списка фильмов с пагинацией и фильтрацией.
     *
     * Endpoint: GET /api/films
     */
    public function index(Request $request): SuccessResponse
    {
        $query = Film::query()->where('status', 'ready');

        if ($request->has('genre')) {
            $query->whereHas('genres', fn ($q) => $q->where('name', $request->genre));
        }

        $orderBy = $request->get('order_by', 'released');
        $orderTo = $request->get('order_to', 'desc');

        $query->orderBy($orderBy, $orderTo);

        return new SuccessResponse($query->paginate(8));
    }

    /**
     * Получение информации о данном фильме.
     *
     * Endpoint: GET /api/films/{id}
     */
    public function show(Request $request, int $filmId): SuccessResponse
    {
        $film = Film::with('genres')->findOrFail($filmId);

        $data = $film->toArray();

        if ($request->user()) {
            $data['is_favorite'] = $request->user()
                ->favoriteFilms()
                ->where('film_id', $filmId)
                ->exists();
        }

        return new SuccessResponse($data);
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
        $film = Film::findOrFail($filmId);

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'sometimes|string|max:1000',
            'director' => 'sometimes|array',
            'starring' => 'sometimes|array',
            'genre' => 'sometimes|array',
            'run_time' => 'sometimes|integer',
            'released' => 'sometimes|integer',
            'status' => 'sometimes|in:pending,moderate,ready',
            'is_promo' => 'sometimes|boolean',
        ]);

        $film->update($request->except('genre'));

        if ($request->has('genre')) {
            $genreIds = $this->genreService->findOrCreateByNames($request->input('genre', []));
            $film->genres()->sync($genreIds);
        }

        return new SuccessResponse($film);
    }

    /**
     * Получение списка похожих фильмов.
     *
     * Endpoint: GET /api/films/{id}/similar
     */
    public function similar(int $filmId): SuccessResponse
    {
        $film = Film::with('genres')->findOrFail($filmId);

        $genreIds = $film->genres->pluck('id');

        $similar = Film::where('id', '!=', $filmId)
            ->where('status', 'ready')
            ->whereHas('genres', fn ($q) => $q->whereIn('genres.id', $genreIds))
            ->limit(4)
            ->get();

        return new SuccessResponse($similar);
    }
}

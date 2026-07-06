<?php

namespace App\Http\Controllers;

use App\Http\Requests\FilmListRequest;
use App\Http\Requests\StoreFilmRequest;
use App\Http\Requests\UpdateFilmRequest;
use App\Http\Responses\SuccessResponse;
use App\Jobs\UpdateFilmJob;
use App\Models\Film;
use App\Services\GenreService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;

final class FilmController extends Controller
{
    public function __construct(private readonly GenreService $genreService)
    {
    }

    /**
     * Получение списка фильмов с пагинацией и фильтрацией.
     *
     * Endpoint: GET /api/films
     */
    public function index(FilmListRequest $request): SuccessResponse
    {
        $genre = $request->validated('genre');
        $orderBy = $request->validated('order_by', 'released');
        $orderTo = $request->validated('order_to', 'desc');
        $page = $request->validated('page', 1);
        $status = 'ready';

        $user = $request->user('sanctum');

        if ($user !== null && $user->isModerator()) {
            $status = $request->validated('status', 'ready');
        }

        $version = Cache::get('films_cache_version', 1);
        $cacheKey = "films_v{$version}_{$status}_{$genre}_{$orderBy}_{$orderTo}_{$page}";

        $films = Cache::remember($cacheKey, 3600, function () use ($genre, $orderBy, $orderTo, $status) {
            $query = Film::query()->where('status', $status);

            if ($genre !== null) {
                $query->whereHas('genres', fn ($q) => $q->where('name', $genre));
            }

            $query->orderBy($orderBy, $orderTo);

            return $query->paginate(8);
        });

        return new SuccessResponse($films);
    }

    /**
     * Получение информации о данном фильме.
     *
     * Endpoint: GET /api/films/{id}
     */
    public function show(Request $request, Film $film): SuccessResponse
    {
        $film->load('genres');

        $data = $film->toArray();

        $user = $request->user('sanctum');

        if ($user !== null) {
            $data['is_favorite'] = $user
                ->favoriteFilms()
                ->where('film_id', $film->id)
                ->exists();
        }

        return new SuccessResponse($data);
    }

    /**
     * Добавление нового фильма в базу (только для модератора).
     *
     * Endpoint: POST /api/films
     */
    public function store(StoreFilmRequest $request): SuccessResponse
    {
        $film = Film::create([
            'imdb_id' => $request->validated('imdb_id'),
            'status' => 'pending',
        ]);

        UpdateFilmJob::dispatch($film->imdb_id);
        $this->invalidateFilmsCache();

        return new SuccessResponse($film, 201);
    }

    /**
     * Инвалидирует кэш списков фильмов, увеличивая версию
     */
    private function invalidateFilmsCache(): void
    {
        Cache::add('films_cache_version', 1);
        Cache::increment('films_cache_version');
        Cache::forget('promo_film');
    }

    /**
     * Редактирование фильма.
     *
     * Endpoint: PATCH /api/films/{id}
     */
    public function update(UpdateFilmRequest $request, Film $film): SuccessResponse
    {
        $film->update($request->safe()->except('genre'));

        if ($request->has('genre')) {
            $genreIds = $this->genreService->findOrCreateByNames($request->validated('genre', []));
            $film->genres()->sync($genreIds);
        }

        $this->invalidateFilmsCache();

        return new SuccessResponse($film);
    }

    /**
     * Получение списка похожих фильмов.
     *
     * Endpoint: GET /api/films/{id}/similar
     */
    public function similar(Film $film): SuccessResponse
    {
        $film->load('genres');

        $genreIds = $film->genres->pluck('id');

        $similar = Film::where('id', '!=', $film->id)
            ->where('status', 'ready')
            ->whereHas('genres', fn ($q) => $q->whereIn('genres.id', $genreIds))
            ->limit(4)
            ->get();

        return new SuccessResponse($similar);
    }
}

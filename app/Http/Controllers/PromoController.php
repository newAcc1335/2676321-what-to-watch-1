<?php

namespace App\Http\Controllers;

use App\Http\Responses\SuccessResponse;
use App\Models\Film;
use Illuminate\Support\Facades\Cache;

final class PromoController extends Controller
{
    /**
     * Получение текущего промо-фильма.
     *
     * Endpoint: GET /api/promo
     */
    public function show(): SuccessResponse
    {
        $film = Cache::remember('promo_film', 3600, function () {
            return Film::where('is_promo', true)->firstOrFail();
        });

        return new SuccessResponse($film);
    }

    /**
     * Установка промо-фильма.
     *
     * Endpoint: POST /api/promo/{film}
     */
    public function store(Film $film): SuccessResponse
    {
        Film::where('is_promo', true)->update(['is_promo' => false]);
        $film->update(['is_promo' => true]);

        Cache::forget('promo_film');

        return new SuccessResponse($film);
    }
}

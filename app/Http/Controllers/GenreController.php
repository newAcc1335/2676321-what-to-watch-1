<?php

namespace App\Http\Controllers;

use App\Http\Responses\SuccessResponse;
use Illuminate\Http\Request;
use App\Models\Genre;

class GenreController extends Controller
{
    /**
     * Получение списка всех жанров.
     *
     * Endpoint: GET /api/genres
     */
    public function index(): SuccessResponse
    {
        return new SuccessResponse(Genre::all());
    }

    /**
     * Редактирование жанра.
     *
     * Endpoint: PATCH /api/genres/{genre}
     */
    public function update(Request $request, Genre $genre): SuccessResponse
    {
        $request->validate([
            'name' => 'required|string|unique:genres,name,' . $genre->id,
        ]);

        $genre->update(['name' => $request->name]);

        return new SuccessResponse($genre);
    }
}

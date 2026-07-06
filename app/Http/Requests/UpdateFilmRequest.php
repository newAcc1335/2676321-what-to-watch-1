<?php

namespace App\Http\Requests;

use App\Models\Film;
use App\Rules\ImdbIdRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateFilmRequest extends FormRequest
{
    /**
     * Авторизует запрос
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Правила валидации редактирования фильма
     */
    public function rules(): array
    {
        /** @var Film $film */
        $film = $this->route('film');

        return [
            'name' => 'required|string|max:255',
            'poster_image' => 'nullable|string|max:255',
            'preview_image' => 'nullable|string|max:255',
            'background_image' => 'nullable|string|max:255',
            'background_color' => 'nullable|string|max:9',
            'video_link' => 'nullable|string|max:255',
            'preview_video_link' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'director' => 'nullable|array',
            'director.*' => 'string|max:255',
            'starring' => 'nullable|array',
            'starring.*' => 'string|max:255',
            'genre' => 'nullable|array',
            'genre.*' => 'string|max:255',
            'run_time' => 'nullable|integer',
            'released' => 'nullable|integer',
            'imdb_id' => [
                'required',
                'string',
                Rule::unique('films', 'imdb_id')->ignore($film),
                new ImdbIdRule(),
            ],
            'status' => 'required|in:pending,moderate,ready',
        ];
    }
}

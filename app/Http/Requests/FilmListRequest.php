<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class FilmListRequest extends FormRequest
{
    /**
     * Авторизует запрос
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Правила валидации параметров списка фильмов
     */
    public function rules(): array
    {
        return [
            'page' => 'nullable|integer|min:1',
            'genre' => 'nullable|string|max:255',
            'status' => 'nullable|in:pending,moderate,ready',
            'order_by' => 'nullable|in:released,rating',
            'order_to' => 'nullable|in:asc,desc',
        ];
    }
}

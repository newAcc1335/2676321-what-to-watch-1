<?php

namespace App\Http\Requests;

use App\Rules\ImdbIdRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreFilmRequest extends FormRequest
{
    /**
     * Авторизует запрос
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Правила валидации заявки на добавление фильма
     */
    public function rules(): array
    {
        return [
            'imdb_id' => ['required', 'string', 'unique:films,imdb_id', new ImdbIdRule()],
        ];
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class LoginRequest extends FormRequest
{
    /**
     * Авторизует запрос
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Правила валидации запроса на вход
     */
    public function rules(): array
    {
        return [
            'email' => 'required|email',
            'password' => 'required|string',
        ];
    }
}

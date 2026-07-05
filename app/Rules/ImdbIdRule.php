<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ImdbIdRule implements ValidationRule
{
    /**
     * Проверяет, что значение соответствует формату IMDB ID (ttXXXXXXX).
     */
    #[\Override]
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! preg_match('/^tt\d+$/', $value)) {
            $fail('Поле должно быть в формате ttXXXXXXX');
        }
    }
}

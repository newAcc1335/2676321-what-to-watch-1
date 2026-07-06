<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

final class CheckRole
{
    /**
     * Проверяет наличие роли у пользователя перед допуском к роуту.
     *
     * @param  string  $role  название метода модели User для проверки роли
     *
     * @throws AuthorizationException
     */
    public function handle(Request $request, Closure $next, string $role): mixed
    {
        if (! Auth::check() || ! Auth::user()->$role()) {
            throw new AuthorizationException();
        }

        return $next($request);
    }
}

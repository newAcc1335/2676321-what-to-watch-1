<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $role): mixed
    {
        if (! Auth::check() || ! Auth::user()->$role()) {
            throw new AuthorizationException;
        }

        return $next($request);
    }
}

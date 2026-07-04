<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Responses\SuccessResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * Регистрация пользователя.
     *
     * Endpoint: POST /api/register
     */
    public function register(Request $request): SuccessResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'file' => 'nullable|image|max:10240',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
        ]);

        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('avatars', 'public');
            $user->update(['avatar' => $path]);
        }

        $token = $user->createToken('auth-token');

        return new SuccessResponse([
            'token' => $token->plainTextToken,
        ], 201);
    }

    /**
     * Авторизация пользователя.
     *
     * Endpoint: POST /api/login
     */
    public function login(LoginRequest $request): SuccessResponse
    {
        if (! Auth::attempt($request->validated())) {
            abort(401, trans('auth.failed'));
        }

        $token = Auth::user()->createToken('auth-token');

        return new SuccessResponse([
            'token' => $token->plainTextToken,
        ]);
    }

    /**
     * Выход из системы.
     *
     * Endpoint: POST /api/logout
     */
    public function logout(Request $request): SuccessResponse
    {
        $request->user()->tokens()->delete();

        return new SuccessResponse;
    }
}

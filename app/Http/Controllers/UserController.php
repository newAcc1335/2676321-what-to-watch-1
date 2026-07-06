<?php

namespace App\Http\Controllers;

use App\Http\Responses\SuccessResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    /**
     * Получение профиля пользователя.
     *
     * Endpoint: GET /api/user
     */
    public function show(Request $request): SuccessResponse
    {
        return new SuccessResponse($request->user());
    }

    /**
     * Обновление профиля пользователя.
     *
     * Endpoint: PATCH /api/user
     */
    public function update(Request $request): SuccessResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . (string) $request->user()->id,
            'password' => 'sometimes|string|min:8',
            'file' => 'nullable|image|max:10240',
        ]);

        $user = $request->user();

        $user->update($request->only(['name', 'email', 'password']));

        if ($request->hasFile('file')) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $path = $request->file('file')->store('avatars', 'public');
            $user->update(['avatar' => $path]);
        }

        return new SuccessResponse($user);
    }
}

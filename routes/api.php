<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\FilmController;
use App\Http\Controllers\GenreController;
use App\Http\Controllers\PromoController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/films', [FilmController::class, 'index']);
Route::get('/films/{film}', [FilmController::class, 'show']);
Route::get('/films/{film}/similar', [FilmController::class, 'similar']);

Route::get('/comments/{film}', [CommentController::class, 'index']);
Route::get('/genres', [GenreController::class, 'index']);
Route::get('/promo', [PromoController::class, 'show']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/favorite', [FavoriteController::class, 'index']);
    Route::post('/films/{film}/favorite', [FavoriteController::class, 'store']);
    Route::delete('/films/{film}/favorite', [FavoriteController::class, 'destroy']);

    Route::post('/comments/{film}', [CommentController::class, 'store']);
    Route::patch('/comments/{comment}', [CommentController::class, 'update']);
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy']);

    Route::get('/user', [UserController::class, 'show']);
    Route::patch('/user', [UserController::class, 'update']);

    Route::post('/logout', [AuthController::class, 'logout']);
});

Route::middleware(['auth:sanctum', 'role:isModerator'])->group(function () {
    Route::post('/films', [FilmController::class, 'store']);
    Route::patch('/films/{film}', [FilmController::class, 'update']);

    Route::patch('/genres/{genre}', [GenreController::class, 'update']);

    Route::post('/promo/{film}', [PromoController::class, 'store']);
});

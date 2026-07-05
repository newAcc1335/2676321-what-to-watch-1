<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Favorite extends Model
{
    protected $fillable = ['user_id', 'film_id'];

    /** Фильм в избранном */
    public function film(): BelongsTo
    {
        return $this->belongsTo(Film::class);
    }

    /** Пользователь добавивший фильм в избранное */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

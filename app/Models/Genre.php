<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

final class Genre extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    /** Фильмы данного жанра */
    public function films(): BelongsToMany
    {
        return $this->belongsToMany(Film::class);
    }
}

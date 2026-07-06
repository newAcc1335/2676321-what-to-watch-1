<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['name', 'email', 'password', 'avatar', 'role'])]
#[Hidden(['password'])]
final class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use Notifiable;

    /** Фильмы в списке «К просмотру» */
    public function favoriteFilms(): BelongsToMany
    {
        return $this->belongsToMany(Film::class, 'favorites')->withTimestamps();
    }

    /** Комментарии пользователя */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /** Проверяет, является ли пользователь модератором */
    public function isModerator(): bool
    {
        return $this->role === 'moderator';
    }

    #[\Override]
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }
}

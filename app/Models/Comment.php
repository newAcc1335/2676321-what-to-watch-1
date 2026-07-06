<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = ['film_id', 'user_id', 'comment_id', 'text', 'rating'];

    /** Фильм к которому относится комментарий */
    public function film(): BelongsTo
    {
        return $this->belongsTo(Film::class);
    }

    /** Автор комментария */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Родительский комментарий */
    public function parentComment(): BelongsTo
    {
        return $this->belongsTo(Comment::class, 'comment_id');
    }

    /** Ответы на этот комментарий */
    public function childComments(): HasMany
    {
        return $this->hasMany(Comment::class, 'comment_id');
    }

    /** Возвращает имя автора или 'Гость' для анонимных комментариев */
    protected function getAuthorNameAttribute(): string
    {
        return $this->user?->name ?? 'Гость';
    }
}

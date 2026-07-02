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

    public function film(): BelongsTo
    {
        return $this->belongsTo(Film::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function parentComment(): BelongsTo
    {
        return $this->belongsTo(Comment::class, 'comment_id');
    }

    public function childComments(): HasMany
    {
        return $this->hasMany(Comment::class, 'comment_id');
    }

    public function getAuthorNameAttribute(): string
    {
        return $this->user?->name ?? 'incognito';
    }
}

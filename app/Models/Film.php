<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Film extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'poster_image',
        'preview_image',
        'background_image',
        'background_color',
        'video_link',
        'preview_video_link',
        'description',
        'director',
        'starring',
        'run_time',
        'released',
        'rating',
        'scores_count',
        'imdb_id',
        'status',
        'is_promo',
    ];

    /** Жанры фильма */
    public function genres(): BelongsToMany
    {
        return $this->belongsToMany(Genre::class);
    }

    /** Записи в избранном */
    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    /** Пересчитывает рейтинг фильма на основе оценок в комментариях */
    public function updateRating(): void
    {
        $this->rating = $this->comments()->whereNotNull('rating')->avg('rating');
        $this->scores_count = $this->comments()->whereNotNull('rating')->count();
        $this->save();
    }

    /** Комментарии к фильму */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    #[\Override]
    protected function casts(): array
    {
        return [
            'starring' => 'array',
            'director' => 'array',
            'is_promo' => 'boolean',
        ];
    }
}

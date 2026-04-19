<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Game extends Model
{
    use HasFactory;

    protected $table = 'games';

    protected $fillable = [
        'provider_id',
        'game_server_url',
        'game_id',
        'game_name',
        'game_code',
        'game_type',
        'description',
        'cover',
        'status',
        'technology',
        'has_lobby',
        'is_mobile',
        'has_freespins',
        'has_tables',
        'only_demo',
        'rtp',
        'distribution',
        'views',
        'is_featured',
        'show_home',
    ];

    protected $appends = [
        'hasFavorite',
        'totalFavorites',
        'hasLike',
        'totalLikes',
        'dateHumanReadable',
        'createdAt',
    ];

    public function getTotalFavoritesAttribute(): int
    {
        try {
            return (int) $this->favorites()->count();
        } catch (\Throwable $e) {
            return 0;
        }
    }

    public function getTotalLikesAttribute(): int
    {
        try {
            return (int) $this->likes()->count();
        } catch (\Throwable $e) {
            return 0;
        }
    }

    public function getHasLikeAttribute(): bool
    {
        try {
            if (auth('api')->check() && !empty($this->attributes['id'])) {
                $like = GameLike::where('user_id', auth('api')->id())
                    ->where('game_id', $this->attributes['id'])
                    ->first();

                return !empty($like);
            }

            return false;
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function getHasFavoriteAttribute(): bool
    {
        try {
            if (auth('api')->check() && !empty($this->attributes['id'])) {
                $favorite = GameFavorite::where('user_id', auth('api')->id())
                    ->where('game_id', $this->attributes['id'])
                    ->first();

                return !empty($favorite);
            }

            return false;
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class, 'provider_id', 'id');
    }

    public function categories(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(GameFavorite::class);
    }

    public function likes(): HasMany
    {
        return $this->hasMany(GameLike::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(GameReview::class);
    }

    public function getCreatedAtAttribute()
    {
        return !empty($this->attributes['created_at'])
            ? Carbon::parse($this->attributes['created_at'])->format('Y-m-d')
            : null;
    }

    public function getDateHumanReadableAttribute()
    {
        return !empty($this->attributes['created_at'])
            ? Carbon::parse($this->attributes['created_at'])->diffForHumans()
            : null;
    }
}
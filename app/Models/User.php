<?php

namespace App\Models;

use Carbon\Carbon;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements FilamentUser, JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'role_id',
        'avatar',
        'name',
        'last_name',
        'cpf',
        'phone',
        'email',
        'password',
        'logged_in',
        'banned',
        'inviter',
        'inviter_code',
        'affiliate_revenue_share',
        'affiliate_revenue_share_fake',
        'affiliate_cpa',
        'affiliate_baseline',
        'is_demo_agent',
        'is_admin',
        'language',
        'role_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    protected $appends = ['dateHumanReadable', 'createdAt', 'totalLikes'];

    public function favorites(): HasMany
    {
        return $this->hasMany(GameFavorite::class);
    }

    public function likes(): HasMany
    {
        return $this->hasMany(Like::class);
    }

    protected function password(): Attribute
    {
        return Attribute::make(
            set: fn (string $value) => Hash::make($value),
        );
    }

    public function affiliate(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inviter', 'id');
    }

    public function wallet(): HasOne
    {
        return $this->hasOne(Wallet::class)->where('active', 1);
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'model_has_roles', 'model_id', 'role_id');
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->hasRole(['admin', 'afiliado']) && $this->email === 'admin@hotmail.com';
    }

    public function getTotalLikesAttribute(): int
    {
        try {
            if (!Schema::hasTable('likes')) {
                return 0;
            }

            return (int) $this->likes()->count();
        } catch (\Throwable $e) {
            return 0;
        }
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

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    /**
     * Get the user's favorite cam models
     */
    public function favorites(): BelongsToMany
    {
        return $this->belongsToMany(CamModel::class, 'user_favorites', 'user_id', 'cam_model_id')
            ->withTimestamps();
    }

    /**
     * Check if user has favorited a model
     */
    public function hasFavorited(CamModel $model): bool
    {
        return $this->favorites()->where('cam_model_id', $model->id)->exists();
    }

    /**
     * Toggle favorite status for a model
     */
    public function toggleFavorite(CamModel $model): bool
    {
        if ($this->hasFavorited($model)) {
            $this->favorites()->detach($model->id);
            return false;
        }
        
        $this->favorites()->attach($model->id);
        return true;
    }

    /**
     * Get online favorites
     */
    public function onlineFavorites()
    {
        return $this->favorites()->where('is_online', true)->orderBy('viewers_count', 'desc');
    }
}

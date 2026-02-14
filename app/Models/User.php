<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
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
     * Get the IDs of favorited cam models from the local database.
     *
     * Note: user_favorites lives on the local DB while cam_models lives on the
     * external "cam" DB, so we cannot use a belongsToMany JOIN. Instead we
     * query the pivot table directly and then fetch CamModels via whereIn.
     */
    public function favoriteCamModelIds(): Collection
    {
        return DB::table('user_favorites')
            ->where('user_id', $this->id)
            ->pluck('cam_model_id');
    }

    /**
     * Get the user's favorite cam models (cross-database safe).
     *
     * Returns an Eloquent Builder so callers can chain ->orderBy(), ->get(), etc.
     */
    public function favorites()
    {
        return CamModel::whereIn('id', $this->favoriteCamModelIds());
    }

    /**
     * Check if user has favorited a model
     */
    public function hasFavorited(CamModel $model): bool
    {
        return DB::table('user_favorites')
            ->where('user_id', $this->id)
            ->where('cam_model_id', $model->id)
            ->exists();
    }

    /**
     * Toggle favorite status for a model
     */
    public function toggleFavorite(CamModel $model): bool
    {
        if ($this->hasFavorited($model)) {
            DB::table('user_favorites')
                ->where('user_id', $this->id)
                ->where('cam_model_id', $model->id)
                ->delete();
            return false;
        }

        DB::table('user_favorites')->insert([
            'user_id' => $this->id,
            'cam_model_id' => $model->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        return true;
    }

    /**
     * Get online favorites
     */
    public function onlineFavorites()
    {
        return CamModel::whereIn('id', $this->favoriteCamModelIds())
            ->where('is_online', true)
            ->orderBy('viewers_count', 'desc');
    }
}

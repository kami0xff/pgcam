<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ModelDescriptionTranslation extends Model
{
    protected $fillable = [
        'model_description_id',
        'locale',
        'short_description',
        'long_description',
        'specialties',
    ];

    /**
     * Get the parent description
     */
    public function description(): BelongsTo
    {
        return $this->belongsTo(ModelDescription::class, 'model_description_id');
    }

    /**
     * Find or create translation for a description
     */
    public static function findOrCreateForDescription(int $descriptionId, string $locale): self
    {
        return static::firstOrCreate(
            ['model_description_id' => $descriptionId, 'locale' => $locale],
            ['short_description' => '', 'long_description' => '']
        );
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModelSeoContent extends Model
{
    protected $table = 'model_seo_content';

    protected $fillable = [
        'cam_model_id',
        'locale',
        'meta_title',
        'meta_description',
        'long_description',
        'custom_content',
    ];

    /**
     * Get SEO content for a model
     */
    public static function forModel(int $modelId, string $locale = 'en'): ?self
    {
        return self::where('cam_model_id', $modelId)
            ->where('locale', $locale)
            ->first();
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CountryTranslation extends Model
{
    protected $fillable = [
        'country_id',
        'locale',
        'slug',
        'name',
        'meta_title',
        'meta_description',
        'page_content',
    ];

    /**
     * Get the parent country
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }
}

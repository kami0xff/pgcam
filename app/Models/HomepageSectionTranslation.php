<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HomepageSectionTranslation extends Model
{
    protected $fillable = [
        'homepage_section_id',
        'locale',
        'title',
        'meta_description',
    ];

    /**
     * Get the parent section
     */
    public function section(): BelongsTo
    {
        return $this->belongsTo(HomepageSection::class, 'homepage_section_id');
    }
}

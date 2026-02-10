<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TagTranslation extends Model
{
    protected $fillable = [
        'tag_id',
        'locale',
        'slug',
        'name',
        'meta_title',
        'meta_description',
        'page_content',
    ];

    /**
     * Get the parent tag
     */
    public function tag(): BelongsTo
    {
        return $this->belongsTo(Tag::class);
    }
}

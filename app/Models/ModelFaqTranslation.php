<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ModelFaqTranslation extends Model
{
    protected $fillable = [
        'model_faq_id',
        'locale',
        'question',
        'answer',
    ];

    /**
     * Get the parent FAQ
     */
    public function faq(): BelongsTo
    {
        return $this->belongsTo(ModelFaq::class, 'model_faq_id');
    }
}

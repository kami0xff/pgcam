<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TipActionTranslation extends Model
{
    protected $fillable = [
        'tip_action_type_id',
        'locale',
        'name',
        'description',
    ];

    /**
     * Get the action type this translation belongs to
     */
    public function actionType(): BelongsTo
    {
        return $this->belongsTo(TipActionType::class, 'tip_action_type_id');
    }
}

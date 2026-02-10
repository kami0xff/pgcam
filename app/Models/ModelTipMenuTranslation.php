<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ModelTipMenuTranslation extends Model
{
    protected $fillable = [
        'model_tip_menu_item_id',
        'locale',
        'action_name',
    ];

    /**
     * Get the parent tip menu item
     */
    public function tipMenuItem(): BelongsTo
    {
        return $this->belongsTo(ModelTipMenuItem::class, 'model_tip_menu_item_id');
    }
}

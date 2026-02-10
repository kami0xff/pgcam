<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ModelTipMenu extends Model
{
    protected $fillable = [
        'model_id',
        'tip_action_type_id',
        'token_price',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'token_price' => 'integer',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Get the action type for this menu item
     */
    public function actionType(): BelongsTo
    {
        return $this->belongsTo(TipActionType::class, 'tip_action_type_id');
    }

    /**
     * Scope to active items
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get tip menu for a specific model
     */
    public static function getForModel(string $modelId): \Illuminate\Support\Collection
    {
        return static::where('model_id', $modelId)
            ->active()
            ->with('actionType')
            ->orderBy('sort_order')
            ->orderBy('token_price')
            ->get();
    }

    /**
     * Generate default tip menu for a model based on their tags
     */
    public static function generateDefaultForModel(string $modelId, array $tags = []): void
    {
        // Get all active action types
        $actionTypes = TipActionType::active()->get();

        foreach ($actionTypes as $action) {
            // Calculate a random price within the suggested range
            $price = rand($action->suggested_min_tokens, $action->suggested_max_tokens);
            
            // Round to nice numbers (5, 10, 15, 20, etc.)
            $price = (int) round($price / 5) * 5;
            $price = max(5, $price); // Minimum 5 tokens

            static::updateOrCreate(
                [
                    'model_id' => $modelId,
                    'tip_action_type_id' => $action->id,
                ],
                [
                    'token_price' => $price,
                    'is_active' => true,
                    'sort_order' => $action->sort_order,
                ]
            );
        }
    }
}

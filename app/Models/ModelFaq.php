<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\App;

class ModelFaq extends Model
{
    protected $table = 'model_faqs';

    protected $fillable = [
        'model_id',  // Updated from cam_model_id
        'locale',    // Base locale (usually 'en')
        'question',
        'answer',
        'sort_order',
    ];

    /**
     * Get translations for this FAQ
     */
    public function translations(): HasMany
    {
        return $this->hasMany(ModelFaqTranslation::class);
    }

    /**
     * Get translation for a specific locale
     */
    public function translation(?string $locale = null): ?ModelFaqTranslation
    {
        $locale = $locale ?? App::getLocale();
        
        if ($locale === 'en' || $locale === $this->locale) {
            return null; // Base content is in this model
        }
        
        return $this->translations()->where('locale', $locale)->first();
    }

    /**
     * Get localized question
     */
    public function getLocalizedQuestionAttribute(): string
    {
        $translation = $this->translation();
        return $translation?->question ?? $this->question;
    }

    /**
     * Get localized answer
     */
    public function getLocalizedAnswerAttribute(): string
    {
        $translation = $this->translation();
        return $translation?->answer ?? $this->answer;
    }

    /**
     * Get FAQs for a model in current locale
     */
    public static function forModel(string $modelId, ?string $locale = null): \Illuminate\Database\Eloquent\Collection
    {
        $locale = $locale ?? App::getLocale();
        
        $faqs = self::where('model_id', $modelId)
            ->orderBy('sort_order')
            ->get();

        // If not English, load translations
        if ($locale !== 'en') {
            $faqs->load(['translations' => function ($query) use ($locale) {
                $query->where('locale', $locale);
            }]);
        }

        return $faqs;
    }

    /**
     * Get FAQ Schema.org JSON-LD
     */
    public static function getSchemaForModel(string $modelId, ?string $locale = null): ?array
    {
        $faqs = self::forModel($modelId, $locale);
        
        if ($faqs->isEmpty()) {
            return null;
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => $faqs->map(fn($faq) => [
                '@type' => 'Question',
                'name' => $faq->localized_question,
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => $faq->localized_answer,
                ],
            ])->toArray(),
        ];
    }

    /**
     * Scope to FAQs without a specific translation
     */
    public function scopeWithoutTranslation($query, string $locale)
    {
        return $query->whereDoesntHave('translations', function ($q) use ($locale) {
            $q->where('locale', $locale);
        });
    }
}

<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class TranslationService
{
    protected ?string $apiKey;
    protected string $model = 'claude-3-haiku-20240307'; // Fast & cheap for translations
    protected array $localesConfig;

    public function __construct()
    {
        $this->apiKey = config('services.anthropic.api_key') ?? '';
        $this->localesConfig = config('locales.supported', []);
    }

    /**
     * Translate a tag and generate SEO content
     */
    public function translateTag(string $tagName, string $targetLocale): array
    {
        $cacheKey = "tag_translation:{$tagName}:{$targetLocale}";
        
        return Cache::remember($cacheKey, 86400, function () use ($tagName, $targetLocale) {
            $prompt = $this->buildTagTranslationPrompt($tagName, $targetLocale);
            $response = $this->callAnthropic($prompt);
            
            return $this->parseTagTranslation($response, $tagName, $targetLocale);
        });
    }

    /**
     * Generate FAQs for a cam model
     */
    public function generateModelFAQs(array $modelData, string $locale = 'en'): array
    {
        $prompt = $this->buildFAQPrompt($modelData, $locale);
        $response = $this->callAnthropic($prompt);
        
        return $this->parseFAQResponse($response);
    }

    /**
     * Generate SEO description for a model
     */
    public function generateModelDescription(array $modelData, string $locale = 'en'): array
    {
        $prompt = $this->buildDescriptionPrompt($modelData, $locale);
        $response = $this->callAnthropic($prompt);
        
        return $this->parseDescriptionResponse($response);
    }

    /**
     * Translate country name and generate SEO content
     */
    public function translateCountry(string $countryName, string $countryCode, string $targetLocale): array
    {
        $cacheKey = "country_translation:{$countryCode}:{$targetLocale}";
        
        return Cache::remember($cacheKey, 86400 * 7, function () use ($countryName, $countryCode, $targetLocale) {
            $prompt = $this->buildCountryTranslationPrompt($countryName, $countryCode, $targetLocale);
            $response = $this->callAnthropic($prompt);
            
            return $this->parseCountryTranslation($response, $countryName, $targetLocale);
        });
    }

    /**
     * Bulk translate tags
     */
    public function bulkTranslateTags(array $tags, string $targetLocale): array
    {
        $results = [];
        
        foreach (array_chunk($tags, 10) as $chunk) {
            $prompt = $this->buildBulkTagPrompt($chunk, $targetLocale);
            $response = $this->callAnthropic($prompt);
            $results = array_merge($results, $this->parseBulkTagResponse($response, $chunk, $targetLocale));
        }
        
        return $results;
    }

    /**
     * Call Anthropic API
     */
    protected function callAnthropic(string $prompt): string
    {
        if (empty($this->apiKey) || $this->apiKey === '') {
            throw new \Exception('Anthropic API key not configured. Set ANTHROPIC_API_KEY in your .env file.');
        }

        $response = Http::withHeaders([
            'x-api-key' => $this->apiKey,
            'anthropic-version' => '2023-06-01',
            'content-type' => 'application/json',
        ])->post('https://api.anthropic.com/v1/messages', [
            'model' => $this->model,
            'max_tokens' => 2048,
            'messages' => [
                ['role' => 'user', 'content' => $prompt]
            ]
        ]);

        if ($response->failed()) {
            throw new \Exception('Anthropic API error: ' . $response->body());
        }

        return $response->json('content.0.text', '');
    }

    /**
     * Build prompt for tag translation
     */
    protected function buildTagTranslationPrompt(string $tagName, string $targetLocale): string
    {
        $langName = $this->getLanguageName($targetLocale);
        
        return <<<PROMPT
Translate this adult cam site tag to {$langName} and generate SEO content.

Tag: "{$tagName}"

Respond in this exact JSON format:
{
    "name": "translated tag name",
    "slug": "url-friendly-slug-in-target-language",
    "meta_title": "SEO title for tag page (50-60 chars)",
    "meta_description": "SEO description (150-160 chars)",
    "page_content": "2-3 sentences of unique content about this category for SEO"
}

Rules:
- Slug must be lowercase, use hyphens, no special characters
- Content should be natural, not keyword stuffed
- Keep it appropriate for adult content but not explicit
- All text in {$langName}
PROMPT;
    }

    /**
     * Build prompt for FAQ generation
     */
    protected function buildFAQPrompt(array $modelData, string $locale): string
    {
        $langName = $this->getLanguageName($locale);
        $username = $modelData['username'] ?? 'this model';
        $platform = $modelData['platform'] ?? 'the platform';
        $tags = implode(', ', $modelData['tags'] ?? []);
        
        return <<<PROMPT
Generate 5 SEO-optimized FAQs for a cam model profile page in {$langName}.

Model Info:
- Username: {$username}
- Platform: {$platform}
- Tags/Categories: {$tags}

Respond in this exact JSON format:
{
    "faqs": [
        {"question": "...", "answer": "..."},
        {"question": "...", "answer": "..."}
    ]
}

Rules:
- Questions should be what users actually search for
- Include the model's name naturally
- Answers should be helpful and 2-3 sentences
- Cover topics: free viewing, tipping, private shows, schedule, content type
- All text in {$langName}
- Keep appropriate for adult content but not explicit
PROMPT;
    }

    /**
     * Build prompt for model description
     */
    protected function buildDescriptionPrompt(array $modelData, string $locale): string
    {
        $langName = $this->getLanguageName($locale);
        $username = $modelData['username'] ?? 'this model';
        $platform = $modelData['platform'] ?? 'the platform';
        $country = $modelData['country'] ?? '';
        $tags = implode(', ', $modelData['tags'] ?? []);
        $age = $modelData['age'] ?? '';
        
        return <<<PROMPT
Generate SEO content for a cam model profile page in {$langName}.

Model Info:
- Username: {$username}
- Platform: {$platform}
- Country: {$country}
- Age: {$age}
- Tags: {$tags}

Respond in this exact JSON format:
{
    "meta_title": "SEO title (50-60 chars, include username)",
    "meta_description": "SEO description (150-160 chars)",
    "long_description": "3-4 paragraph description for the page (include relevant keywords naturally)"
}

Rules:
- Natural, engaging content
- Include relevant keywords but don't stuff
- All text in {$langName}
- Keep appropriate for adult content
PROMPT;
    }

    /**
     * Build prompt for country translation
     */
    protected function buildCountryTranslationPrompt(string $countryName, string $countryCode, string $targetLocale): string
    {
        $langName = $this->getLanguageName($targetLocale);
        
        return <<<PROMPT
Translate this country page content for a cam site to {$langName}.

Country: {$countryName} ({$countryCode})

Respond in this exact JSON format:
{
    "name": "country name in {$langName}",
    "slug": "url-slug-in-target-language",
    "meta_title": "SEO title for cam models from this country (50-60 chars)",
    "meta_description": "SEO description (150-160 chars)",
    "page_content": "2-3 sentences about cam models from this country"
}

Rules:
- Slug lowercase with hyphens
- Natural content, not keyword stuffed
- All text in {$langName}
PROMPT;
    }

    /**
     * Build prompt for bulk tag translation
     */
    protected function buildBulkTagPrompt(array $tags, string $targetLocale): string
    {
        $langName = $this->getLanguageName($targetLocale);
        $tagList = implode("\n", array_map(fn($t) => "- {$t}", $tags));
        
        return <<<PROMPT
Translate these adult cam site tags to {$langName}.

Tags:
{$tagList}

Respond in this exact JSON format:
{
    "translations": [
        {"original": "tag1", "name": "translated", "slug": "url-slug"},
        {"original": "tag2", "name": "translated", "slug": "url-slug"}
    ]
}

Rules:
- Slug must be lowercase, hyphens only, no special characters
- Maintain the same order as input
- All translations in {$langName}
PROMPT;
    }

    /**
     * Parse tag translation response
     */
    protected function parseTagTranslation(string $response, string $original, string $locale): array
    {
        try {
            $data = json_decode($response, true);
            
            return [
                'name' => $data['name'] ?? $original,
                'slug' => Str::slug($data['slug'] ?? $original),
                'meta_title' => $data['meta_title'] ?? null,
                'meta_description' => $data['meta_description'] ?? null,
                'page_content' => $data['page_content'] ?? null,
            ];
        } catch (\Exception $e) {
            return [
                'name' => $original,
                'slug' => Str::slug($original),
                'meta_title' => null,
                'meta_description' => null,
                'page_content' => null,
            ];
        }
    }

    /**
     * Parse FAQ response
     */
    protected function parseFAQResponse(string $response): array
    {
        try {
            $data = json_decode($response, true);
            return $data['faqs'] ?? [];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Parse description response
     */
    protected function parseDescriptionResponse(string $response): array
    {
        try {
            return json_decode($response, true) ?? [];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Parse country translation response
     */
    protected function parseCountryTranslation(string $response, string $original, string $locale): array
    {
        try {
            $data = json_decode($response, true);
            
            return [
                'name' => $data['name'] ?? $original,
                'slug' => Str::slug($data['slug'] ?? $original),
                'meta_title' => $data['meta_title'] ?? null,
                'meta_description' => $data['meta_description'] ?? null,
                'page_content' => $data['page_content'] ?? null,
            ];
        } catch (\Exception $e) {
            return [
                'name' => $original,
                'slug' => Str::slug($original),
                'meta_title' => null,
                'meta_description' => null,
                'page_content' => null,
            ];
        }
    }

    /**
     * Parse bulk tag response
     */
    protected function parseBulkTagResponse(string $response, array $originals, string $locale): array
    {
        try {
            $data = json_decode($response, true);
            $translations = $data['translations'] ?? [];
            
            $results = [];
            foreach ($translations as $t) {
                $results[$t['original']] = [
                    'name' => $t['name'],
                    'slug' => Str::slug($t['slug']),
                ];
            }
            
            return $results;
        } catch (\Exception $e) {
            return array_combine($originals, array_map(fn($t) => [
                'name' => $t,
                'slug' => Str::slug($t),
            ], $originals));
        }
    }

    /**
     * Get language name from locale code
     */
    public function getLanguageName(string $locale): string
    {
        // Check config first
        if (isset($this->localesConfig[$locale])) {
            return $this->localesConfig[$locale]['name'];
        }

        // Fallback for common locales
        return match($locale) {
            'en' => 'English',
            'es' => 'Spanish',
            'fr' => 'French',
            'de' => 'German',
            'pt' => 'Portuguese',
            'pt-BR' => 'Brazilian Portuguese',
            'it' => 'Italian',
            'nl' => 'Dutch',
            'pl' => 'Polish',
            'ru' => 'Russian',
            'uk' => 'Ukrainian',
            'ja' => 'Japanese',
            'ko' => 'Korean',
            'zh' => 'Chinese (Simplified)',
            'zh-TW' => 'Chinese (Traditional)',
            'ar' => 'Arabic',
            'tr' => 'Turkish',
            'th' => 'Thai',
            'vi' => 'Vietnamese',
            'id' => 'Indonesian',
            'hi' => 'Hindi',
            'sv' => 'Swedish',
            'da' => 'Danish',
            'no' => 'Norwegian',
            'fi' => 'Finnish',
            'cs' => 'Czech',
            'sk' => 'Slovak',
            'hu' => 'Hungarian',
            'ro' => 'Romanian',
            'bg' => 'Bulgarian',
            'el' => 'Greek',
            'he' => 'Hebrew',
            'fa' => 'Persian',
            default => 'English',
        };
    }

    /**
     * Get supported locales
     */
    public function getSupportedLocales(): array
    {
        return array_keys($this->localesConfig);
    }

    /**
     * Get priority locales for bulk operations
     */
    public function getPriorityLocales(): array
    {
        return config('locales.priority', ['en', 'es', 'fr', 'de', 'pt']);
    }

    /**
     * Get locale group
     */
    public function getLocaleGroup(string $group): array
    {
        return config("locales.groups.{$group}", []);
    }

    /**
     * Check if locale is RTL
     */
    public function isRtl(string $locale): bool
    {
        return in_array($locale, config('locales.rtl', []));
    }

    /**
     * Get locale info
     */
    public function getLocaleInfo(string $locale): ?array
    {
        return $this->localesConfig[$locale] ?? null;
    }

    /**
     * Generate SEO content for a page (homepage, index pages, etc.)
     */
    public function generatePageSeoContent(string $pageKey, string $locale = 'en', array $context = []): array
    {
        $cacheKey = "page_seo_content:{$pageKey}:{$locale}";
        
        return Cache::remember($cacheKey, 86400, function () use ($pageKey, $locale, $context) {
            $prompt = $this->buildPageSeoPrompt($pageKey, $locale, $context);
            $response = $this->callAnthropic($prompt);
            
            return $this->parsePageSeoResponse($response);
        });
    }

    /**
     * Build prompt for page SEO content generation
     */
    protected function buildPageSeoPrompt(string $pageKey, string $locale, array $context): string
    {
        $langName = $this->getLanguageName($locale);
        $siteName = 'PornGuru Cam';
        
        $instructions = match($pageKey) {
            'home' => <<<INST
Generate SEO content for the homepage of an adult live cam aggregator site.
The site aggregates live cam models from multiple platforms (XLoveCam, StripChat).

Focus on:
- What the site offers (free live cams, thousands of models, multiple platforms)
- Benefits of using the site (browse all platforms in one place, find your favorite cam girls)
- Keywords: live sex cams, free webcam shows, adult live streaming, cam girls, live porn
- How users can watch free live streams and interact with models
INST,

            'tags_index' => <<<INST
Generate SEO content for the tags/categories browsing page of an adult cam site.
This page lists all categories users can browse (e.g., blonde, teen, milf, asian, etc.)

Focus on:
- The variety of categories available
- How users can find their perfect type of model
- Keywords: cam categories, live cam tags, find cam girls by type, adult webcam categories
- Benefits of organized category browsing
INST,

            'countries_index' => <<<INST
Generate SEO content for the countries browsing page of an adult cam site.
This page lets users find cam models by their country of origin.

Focus on:
- International variety of models available
- Popular countries for cam models (Colombia, Romania, Ukraine, etc.)
- Keywords: cam girls by country, international webcam models, live cams from [region]
- Cultural diversity of models
INST,

            default => <<<INST
Generate generic SEO content for a page on an adult live cam site.
INST
        };

        // Handle specific tag pages
        if (str_starts_with($pageKey, 'tag_')) {
            $tagName = $context['tag_name'] ?? str_replace('tag_', '', $pageKey);
            $instructions = <<<INST
Generate SEO content for a specific category/tag page on an adult cam site.
Tag/Category: {$tagName}

Focus on:
- What makes this category special
- Types of shows and content users can expect
- Why users prefer this category
- Related keywords and search terms
- Natural integration of "{$tagName} cams", "{$tagName} webcam", "live {$tagName} shows"
INST;
        }

        // Handle specific country pages
        if (str_starts_with($pageKey, 'country_')) {
            $countryName = $context['country_name'] ?? str_replace('country_', '', $pageKey);
            $countryCode = $context['country_code'] ?? '';
            $instructions = <<<INST
Generate SEO content for a country-specific page on an adult cam site.
Country: {$countryName} ({$countryCode})

Focus on:
- Characteristics of cam models from this country
- Why users enjoy models from this region
- Cultural aspects that make these models unique
- Natural integration of "{$countryName} cam girls", "{$countryName} webcam models", "live cams from {$countryName}"
INST;
        }

        return <<<PROMPT
You are an SEO content writer for an adult live cam site called "{$siteName}".

{$instructions}

Generate content in {$langName}.

Respond in this exact JSON format:
{
    "title": "Section title/heading (20-50 chars)",
    "content": "3-4 paragraphs of unique SEO content (400-600 words). Include relevant keywords naturally. Use simple sentences. Don't be overly promotional.",
    "keywords": "comma,separated,target,keywords"
}

Important rules:
- Content must be unique and valuable
- NO keyword stuffing - integrate keywords naturally
- Write for humans first, search engines second
- Appropriate for adult content but not explicit
- All text in {$langName}
- Use proper grammar and punctuation
PROMPT;
    }

    /**
     * Parse page SEO content response
     */
    protected function parsePageSeoResponse(string $response): array
    {
        // Clean control characters that break JSON parsing
        // Replace literal newlines/tabs inside strings with proper escapes
        $cleaned = preg_replace('/[\x00-\x1F\x7F]/u', ' ', $response);
        
        // Try to extract JSON from the response (in case there's extra text)
        if (preg_match('/\{[\s\S]*\}/u', $cleaned, $matches)) {
            $cleaned = $matches[0];
        }
        
        $data = json_decode($cleaned, true);
        
        if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
            // Log the error for debugging
            \Log::warning('JSON parse error in parsePageSeoResponse: ' . json_last_error_msg(), [
                'response_length' => strlen($response),
                'response_preview' => substr($response, 0, 200),
            ]);
            
            return [
                'title' => null,
                'content' => '',
                'keywords' => '',
            ];
        }
        
        return [
            'title' => $data['title'] ?? null,
            'content' => $data['content'] ?? '',
            'keywords' => $data['keywords'] ?? '',
        ];
    }

    /**
     * Translate existing page SEO content
     */
    public function translatePageSeoContent(string $title, string $content, string $targetLocale): array
    {
        $langName = $this->getLanguageName($targetLocale);
        
        $prompt = <<<PROMPT
Translate this SEO content for an adult cam site to {$langName}.

Title: {$title}

Content:
{$content}

Respond in this exact JSON format:
{
    "title": "translated title",
    "content": "translated content preserving all formatting and paragraph breaks"
}

Rules:
- Maintain the SEO quality of the content
- Keep natural keyword integration
- All text in {$langName}
- Preserve paragraph structure
PROMPT;

        $response = $this->callAnthropic($prompt);
        
        try {
            return json_decode($response, true) ?? ['title' => $title, 'content' => $content];
        } catch (\Exception $e) {
            return ['title' => $title, 'content' => $content];
        }
    }
}

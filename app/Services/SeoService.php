<?php

namespace App\Services;

use App\Models\CamModel;
use App\Models\Country;
use App\Models\ModelFaq;
use App\Models\Tag;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;

class SeoService
{
    /**
     * Generate Schema.org JSON-LD for a model page
     */
    public function getModelSchema(CamModel $model): array
    {
        $schemas = [];

        // ProfilePage schema
        $schemas[] = [
            '@context' => 'https://schema.org',
            '@type' => 'ProfilePage',
            'dateCreated' => $model->created_at?->toIso8601String(),
            'dateModified' => $model->updated_at?->toIso8601String(),
            'mainEntity' => [
                '@type' => 'Person',
                'name' => $model->username,
                'image' => $model->best_image_url,
                'description' => Str::limit($model->description ?? "Watch {$model->username} live on PornGuru.cam", 160),
                'url' => route('cam-models.show', $model),
                'sameAs' => $model->profile_url ? [$model->profile_url] : [],
                'interactionStatistic' => [
                    '@type' => 'InteractionCounter',
                    'interactionType' => 'https://schema.org/WatchAction',
                    'userInteractionCount' => $model->viewers_count ?? 0,
                ],
            ],
        ];

        // BreadcrumbList schema
        $schemas[] = $this->getBreadcrumbSchema([
            ['name' => 'Home', 'url' => route('home')],
            ['name' => 'Models', 'url' => route('home')],
            ['name' => $model->username, 'url' => route('cam-models.show', $model)],
        ]);

        // VideoObject schema (for live stream)
        if ($model->is_online) {
            $schemas[] = [
                '@context' => 'https://schema.org',
                '@type' => 'VideoObject',
                'name' => "{$model->username} Live Stream",
                'description' => "Watch {$model->username} live cam show",
                'thumbnailUrl' => $model->best_image_url,
                'uploadDate' => now()->toIso8601String(),
                'publication' => [
                    '@type' => 'BroadcastEvent',
                    'isLiveBroadcast' => true,
                    'startDate' => now()->toIso8601String(),
                ],
            ];
        }

        // FAQ schema (if FAQs exist)
        $faqSchema = ModelFaq::getSchemaForModel($model->id, App::getLocale());
        if ($faqSchema) {
            $schemas[] = $faqSchema;
        }

        return $schemas;
    }

    /**
     * Generate Schema.org for tag page
     */
    public function getTagSchema(Tag $tag): array
    {
        return [
            [
                '@context' => 'https://schema.org',
                '@type' => 'CollectionPage',
                'name' => $tag->localized_name . ' Cams',
                'description' => $tag->translation()?->meta_description,
                'url' => $tag->url,
                'numberOfItems' => $tag->models_count,
            ],
            $this->getBreadcrumbSchema([
                ['name' => 'Home', 'url' => route('home')],
                ['name' => 'Tags', 'url' => route('tags.index')],
                ['name' => $tag->localized_name, 'url' => $tag->url],
            ]),
        ];
    }

    /**
     * Generate Schema.org for country page
     */
    public function getCountrySchema(Country $country): array
    {
        return [
            [
                '@context' => 'https://schema.org',
                '@type' => 'CollectionPage',
                'name' => $country->localized_name . ' Cams',
                'description' => $country->translation()?->meta_description,
                'url' => $country->url,
                'numberOfItems' => $country->models_count,
            ],
            $this->getBreadcrumbSchema([
                ['name' => 'Home', 'url' => route('home')],
                ['name' => 'Countries', 'url' => route('countries.index')],
                ['name' => $country->localized_name, 'url' => $country->url],
            ]),
        ];
    }

    /**
     * Generate Schema.org for homepage
     */
    public function getHomepageSchema(int $totalModels, int $onlineModels): array
    {
        return [
            [
                '@context' => 'https://schema.org',
                '@type' => 'WebSite',
                'name' => 'PornGuru.cam',
                'url' => config('app.url'),
                'description' => 'Watch free live cam shows from thousands of models',
                'potentialAction' => [
                    '@type' => 'SearchAction',
                    'target' => [
                        '@type' => 'EntryPoint',
                        'urlTemplate' => config('app.url') . '/?search={search_term_string}',
                    ],
                    'query-input' => 'required name=search_term_string',
                ],
            ],
            [
                '@context' => 'https://schema.org',
                '@type' => 'ItemList',
                'name' => 'Live Cam Models',
                'numberOfItems' => $onlineModels,
                'itemListElement' => [], // Populated dynamically
            ],
        ];
    }

    /**
     * Generate BreadcrumbList schema
     */
    public function getBreadcrumbSchema(array $items): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => array_map(fn($item, $index) => [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'name' => $item['name'],
                'item' => $item['url'],
            ], $items, array_keys($items)),
        ];
    }

    /**
     * Generate hreflang links array
     */
    public function getHreflangLinks(string $baseRoute, array $params = [], array $availableLocales = ['en', 'es', 'fr', 'de', 'pt']): array
    {
        $links = [];
        
        foreach ($availableLocales as $locale) {
            $url = $locale === 'en'
                ? route($baseRoute, $params)
                : route($baseRoute . '.localized', array_merge(['locale' => $locale], $params));
            
            $links[$locale] = $url;
        }
        
        $links['x-default'] = $links['en'];
        
        return $links;
    }

    /**
     * Generate Open Graph tags
     */
    public function getOpenGraphTags(array $data): array
    {
        return [
            'og:type' => $data['type'] ?? 'website',
            'og:title' => $data['title'],
            'og:description' => $data['description'] ?? '',
            'og:url' => $data['url'] ?? url()->current(),
            'og:image' => $data['image'] ?? '',
            'og:site_name' => 'PornGuru.cam',
            'og:locale' => App::getLocale() . '_' . strtoupper(App::getLocale()),
        ];
    }

    /**
     * Generate Twitter Card tags
     */
    public function getTwitterCardTags(array $data): array
    {
        return [
            'twitter:card' => 'summary_large_image',
            'twitter:title' => $data['title'],
            'twitter:description' => $data['description'] ?? '',
            'twitter:image' => $data['image'] ?? '',
        ];
    }

    /**
     * Generate canonical URL with proper locale handling
     */
    public function getCanonicalUrl(string $route, array $params = [], ?string $locale = null): string
    {
        $locale = $locale ?? App::getLocale();
        
        if ($locale === 'en') {
            return route($route, $params);
        }
        
        return route($route . '.localized', array_merge(['locale' => $locale], $params));
    }

    /**
     * Advanced: Generate internal linking suggestions
     */
    public function getInternalLinkSuggestions(CamModel $model): array
    {
        $suggestions = [];

        // Same country models
        if ($model->country) {
            $suggestions['country'] = [
                'title' => "More models from {$model->country}",
                'url' => route('countries.show', Str::slug($model->country)),
            ];
        }

        // Same platform models
        if ($model->source_platform) {
            $suggestions['platform'] = [
                'title' => ucfirst($model->source_platform) . ' Models',
                'url' => route('home', ['platform' => $model->source_platform]),
            ];
        }

        // Tag-based suggestions
        if (!empty($model->tags)) {
            foreach (array_slice($model->tags, 0, 3) as $tag) {
                $suggestions['tags'][] = [
                    'title' => ucfirst(str_replace('-', ' ', $tag)) . ' Cams',
                    'url' => route('tags.show', $tag),
                ];
            }
        }

        return $suggestions;
    }

    /**
     * Advanced: Entity salience optimization
     * Ensures key entities are mentioned with proper density
     */
    public function analyzeContentSalience(string $content, array $targetEntities): array
    {
        $analysis = [];
        $wordCount = str_word_count($content);
        
        foreach ($targetEntities as $entity) {
            $count = substr_count(strtolower($content), strtolower($entity));
            $density = $wordCount > 0 ? ($count / $wordCount) * 100 : 0;
            
            $analysis[$entity] = [
                'count' => $count,
                'density' => round($density, 2),
                'optimal' => $density >= 0.5 && $density <= 2.5,
            ];
        }
        
        return $analysis;
    }
}

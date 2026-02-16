<?php

namespace App\Http\Controllers;

use App\Enums\StripchatTag;
use App\Models\CamModel;
use App\Models\Tag;
use App\Services\SeoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class TagController extends Controller
{
    protected SeoService $seoService;

    // Valid niches from Stripchat
    protected array $validNiches = ['girls', 'couples', 'men', 'trans'];

    public function __construct(SeoService $seoService)
    {
        $this->seoService = $seoService;
    }

    /**
     * List all tags
     */
    public function index(Request $request)
    {
        // Try to get tags from database first (with translations)
        $dbTags = Tag::with('translations')->orderBy('sort_order')->get();

        if ($dbTags->isEmpty()) {
            // Fallback to StripchatTag enum
            $tagsByCategory = $this->getTagsFromEnum();
            $featuredTags = collect($tagsByCategory)
                ->flatten(1)
                ->filter(fn($t) => is_array($t) && in_array($t['slug'] ?? '', $this->getFeaturedSlugs()))
                ->take(20);
        } else {
            // Calculate model counts for each tag (cached for performance)
            $tagCounts = $this->getTagCounts($dbTags);
            
            // Add counts to tags
            $dbTags = $dbTags->map(function ($tag) use ($tagCounts) {
                $tag->models_count = $tagCounts[$tag->slug] ?? 0;
                return $tag;
            });
            
            $tagsByCategory = $dbTags->groupBy('category');
            $featuredTags = $dbTags->where('is_featured', true)->take(20);
        }

        return view('tags.index', [
            'tagsByCategory' => $tagsByCategory,
            'featuredTags' => $featuredTags,
            'niches' => $this->validNiches,
            'useEnum' => $dbTags->isEmpty(),
            'seoSchemas' => [
                $this->seoService->getBreadcrumbSchema([
                    ['name' => __('Home'), 'url' => localized_route('home')],
                    ['name' => __('Tags'), 'url' => localized_route('tags.index')],
                ]),
            ],
        ]);
    }
    
    /**
     * Get model counts for tags (cached for 6 hours)
     */
    protected function getTagCounts($tags): array
    {
        return cache()->remember('tag_model_counts', 21600, function () use ($tags) {
            // Get all expanded tags from the database
            $allTags = \DB::connection('cam')->select("
                SELECT elem as tag, COUNT(DISTINCT sp.username) as cnt
                FROM stripchat_profiles sp, json_array_elements_text(sp.tags) AS elem
                WHERE elem LIKE 'girls/%'
                GROUP BY elem
            ");
            
            // Build lookup: for "girls/young" -> matches "young"
            // for "girls/big-tits-young" -> matches "big-tits", "young", "big-tits-young"
            $counts = [];
            foreach ($tags as $tag) {
                $counts[$tag->slug] = 0;
            }
            
            $slugSet = array_flip(array_keys($counts));
            
            foreach ($allTags as $tc) {
                // "girls/big-tits-young" -> "big-tits-young"
                $parts = explode('/', $tc->tag);
                $fullSlug = end($parts);
                
                // Check exact match first
                if (isset($slugSet[$fullSlug])) {
                    $counts[$fullSlug] += $tc->cnt;
                }
                
                // Check if the tag ends with any of our slugs (e.g., "big-tits-young" ends with "young")
                foreach ($slugSet as $slug => $_) {
                    if ($slug !== $fullSlug) {
                        // Match "xxx-young" for "young" or "young-xxx" 
                        if (str_ends_with($fullSlug, '-' . $slug) || str_starts_with($fullSlug, $slug . '-')) {
                            $counts[$slug] += $tc->cnt;
                        }
                    }
                }
            }
            
            return $counts;
        });
    }

    /**
     * Get tags from the StripchatTag enum
     */
    protected function getTagsFromEnum(): array
    {
        $grouped = [];
        foreach (StripchatTag::grouped() as $category => $tags) {
            $grouped[$category] = collect($tags)->map(fn($tag) => [
                'name' => $tag->label(),
                'slug' => $tag->value,
                'localized_name' => $tag->label(),
            ]);
        }
        return $grouped;
    }

    /**
     * Featured tag slugs
     */
    protected function getFeaturedSlugs(): array
    {
        return [
            'teens', 'young', 'milfs', 'mature',
            'asian', 'ebony', 'latin', 'white',
            'big-tits', 'small-tits', 'big-ass',
            'anal', 'squirt', 'deepthroat', 'fingering',
            'lovense', 'interactive-toy',
            'new', 'hd',
        ];
    }

    /**
     * Show models for a specific tag (all niches combined)
     */
    public function show(Request $request, string $slug)
    {
        $locale = App::getLocale();
        $tag = Tag::findBySlug($slug, $locale);

        if (!$tag) {
            abort(404);
        }

        $query = CamModel::query();

        // Filter by tag using PostgreSQL-compatible text search
        // Always use the ENGLISH slug/name for DB queries (cam DB stores English tags)
        $englishSlug = $tag->slug;
        $englishName = $tag->name;
        $query->where(function ($q) use ($englishSlug, $englishName) {
            $q->whereRaw("tags::text ILIKE ?", ['%"' . $englishSlug . '"%'])
              ->orWhereRaw("tags::text ILIKE ?", ['%/' . $englishSlug . '"%'])
              ->orWhereRaw("tags::text ILIKE ?", ['%"' . $englishName . '"%']);
        });

        // Apply additional filters
        if ($request->boolean('online')) {
            $query->online();
        }

        $query->orderBy('is_online', 'desc')
              ->orderBy('viewers_count', 'desc');

        $models = $query->paginate(48)->withQueryString();
        $translation = $tag->translation($locale);

        return view('tags.show', [
            'tag' => $tag,
            'models' => $models,
            'translation' => $translation,
            'niche' => null, // All niches
            'niches' => $this->validNiches,
            'seoSchemas' => $this->seoService->getTagSchema($tag),
            'hreflangUrls' => $tag->getHreflangUrls(),
        ]);
    }

    /**
     * Show models for a specific niche (girls, couples, men, trans)
     */
    public function niche(Request $request, string $niche)
    {
        if (!in_array($niche, $this->validNiches)) {
            abort(404);
        }

        $query = CamModel::inNiche($niche);

        if ($request->boolean('online')) {
            $query->online();
        }

        $query->orderBy('is_online', 'desc')
              ->orderBy('viewers_count', 'desc');

        $models = $query->paginate(48)->withQueryString();

        // Get popular tags for this niche
        $popularTags = $this->getPopularTagsForNiche($niche);

        return view('niches.show', [
            'niche' => $niche,
            'nicheTitle' => $this->getNicheTitle($niche),
            'models' => $models,
            'popularTags' => $popularTags,
            'niches' => $this->validNiches,
            'seoSchemas' => [
                $this->seoService->getBreadcrumbSchema([
                    ['name' => __('Home'), 'url' => localized_route('home')],
                    ['name' => $this->getNicheTitle($niche), 'url' => localized_route('niche.show', $niche)],
                ]),
            ],
        ]);
    }

    /**
     * Show models for a specific niche + tag combination (e.g., /girls/young or /fr/girls/gros-seins)
     */
    public function nicheTag(Request $request, string $niche, string $tagSlug)
    {
        if (!in_array($niche, $this->validNiches)) {
            abort(404);
        }

        $locale = App::getLocale();
        $tag = Tag::findBySlug($tagSlug, $locale);

        // Use the ENGLISH slug for database queries — tags in cam DB are always English
        $englishSlug = $tag?->slug ?? $tagSlug;
        $tagName = $tag?->localized_name ?? ucwords(str_replace('-', ' ', $tagSlug));
        $fullTag = $niche . '/' . $englishSlug;

        // Query using the English slug (tags stored as English in cam database)
        $query = CamModel::withNicheTag($niche, $englishSlug);

        if ($request->boolean('online')) {
            $query->online();
        }

        $query->orderBy('is_online', 'desc')
              ->orderBy('viewers_count', 'desc');

        $models = $query->paginate(48)->withQueryString();

        // Use localized slug for URLs shown to the user
        $localizedSlug = $tag?->localized_slug ?? $tagSlug;

        return view('niches.tag', [
            'niche' => $niche,
            'nicheTitle' => $this->getNicheTitle($niche),
            'tag' => $tag,
            'tagName' => $tagName,
            'tagSlug' => $localizedSlug,
            'englishTagSlug' => $englishSlug,
            'fullTag' => $fullTag,
            'models' => $models,
            'niches' => $this->validNiches,
            'seoSchemas' => [
                $this->seoService->getBreadcrumbSchema([
                    ['name' => __('Home'), 'url' => localized_route('home')],
                    ['name' => $this->getNicheTitle($niche), 'url' => localized_route('niche.show', $niche)],
                    ['name' => ucfirst($tagName), 'url' => localized_route('niche.tag', [$niche, $localizedSlug])],
                ]),
            ],
        ]);
    }

    /**
     * Get human-readable niche title
     */
    protected function getNicheTitle(string $niche): string
    {
        return match ($niche) {
            'girls' => __('Girls'),
            'couples' => __('Couples'),
            'men' => __('Men'),
            'trans' => __('Trans'),
            default => ucfirst($niche),
        };
    }

    /**
     * Get popular tags for a specific niche
     */
    protected function getPopularTagsForNiche(string $niche): array
    {
        // Return common tags that work with this niche
        return [
            'young', 'mature', 'bbw', 'petite', 'milf', 'asian', 'latina', 
            'ebony', 'blonde', 'brunette', 'redhead', 'big-tits', 'small-tits',
            'anal', 'squirt', 'feet', 'hairy', 'toys', 'lovense',
        ];
    }
}

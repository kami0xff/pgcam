<?php

namespace App\Http\Controllers;

use App\Models\CamModel;
use App\Models\HomepageSection;
use App\Models\ModelDescription;
use App\Models\ModelFaq;
use App\Services\SeoService;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;

class CamModelController extends Controller
{
    public function __construct(
        protected SeoService $seoService
    ) {
    }

    /**
     * Apply common filters to query
     */
    /**
     * Apply filters and sorting to the query.
     * Chaturbate models (no HLS preview) are always pushed to the bottom.
     */
    private function applyFilters(Request $request, $query)
    {
        if ($request->boolean('online')) {
            $query->online();
        }

        if ($request->filled('platform')) {
            $query->platform($request->input('platform'));
        }

        if ($request->filled('gender')) {
            $query->gender($request->input('gender'));
        }

        if ($request->filled('age_min')) {
            $query->where('age', '>=', (int) $request->input('age_min'));
        }
        if ($request->filled('age_max')) {
            $query->where('age', '<=', (int) $request->input('age_max'));
        }

        if ($request->boolean('hd')) {
            $query->hdOnly();
        }

        if ($request->filled('search')) {
            $query->where('username', 'ilike', '%' . $request->input('search') . '%');
        }

        if ($request->filled('tags')) {
            $tags = is_array($request->input('tags'))
                ? $request->input('tags')
                : explode(',', $request->input('tags'));
            $query->withTags($tags);
        }

        if ($request->filled('niche')) {
            $niche = $request->input('niche');
            if (in_array($niche, ['girls', 'couples', 'men', 'trans'])) {
                if ($request->filled('niche_tag')) {
                    $query->withNicheTag($niche, $request->input('niche_tag'));
                } else {
                    $query->inNiche($niche);
                }
            }
        }

        if ($request->filled('country')) {
            $countrySlug = $request->input('country');
            $country = \App\Models\Country::where('slug', $countrySlug)->first();
            if ($country) {
                $query->where(function ($q) use ($country) {
                    $q->where('country', $country->name)
                        ->orWhere('country', $country->code);
                });
            } else {
                $query->where('country', ucwords(str_replace('-', ' ', $countrySlug)));
            }
        }

        // Sorting: HLS-capable platforms first, then chaturbate at the bottom
        $sortField = $request->input('sort', 'viewers_count');
        $sortDirection = $request->input('direction', 'desc');

        $query->orderByRaw("CASE WHEN source_platform = 'chaturbate' THEN 1 ELSE 0 END ASC");
        $query->orderBy('is_online', 'desc');

        if ($sortField === 'goal_progress') {
            $query->whereNotNull('goal_progress')
                ->where('goal_progress', '>', 0)
                ->where('goal_progress', '<', 100)
                ->orderBy('goal_progress', 'desc');
        } else {
            $allowedSorts = ['viewers_count', 'rating', 'favorited_count', 'last_online_at', 'username', 'age'];
            if (in_array($sortField, $allowedSorts)) {
                $query->orderBy($sortField, $sortDirection === 'asc' ? 'asc' : 'desc');
            }
        }

        return $query;
    }

    /**
     * Display the cam models listing with filters
     */
    public function index(Request $request)
    {
        $perPage = 48;
        $page = (int) $request->input('page', 1);
        $hasFilters = $request->hasAny(['search', 'tags', 'gender', 'platform', 'country', 'age_min', 'age_max', 'hd', 'online', 'niche', 'niche_tag']);
        $isDefaultSort = !$request->filled('sort') || $request->input('sort') === 'viewers_count';

        if (!$hasFilters && $isDefaultSort && $page <= 5) {
            // Fast path: UNION approach uses the homepage index (~1ms vs ~190ms).
            // Cached for 30s — cam data refreshes on a similar cadence.
            $cacheKey = 'cam:homepage:p' . $page;
            $models = cache()->remember($cacheKey, 30, function () use ($perPage, $page) {
                return $this->getHomepageModels($perPage, $page);
            });
        } else {
            $query = CamModel::query();
            $this->applyFilters($request, $query);
            $models = $query->simplePaginate($perPage)->withQueryString();
        }

        // Cache filter options and counts — these change slowly
        [$platforms, $genders, $totalCount, $onlineCount] = cache()->remember(
            'cam:homepage_meta',
            60,
            fn () => [
                CamModel::distinct()->pluck('source_platform')->filter()->sort()->values(),
                CamModel::distinct()->pluck('gender')->filter()->sort()->values(),
                CamModel::count(),
                CamModel::online()->count(),
            ]
        );

        // SEO Featured Sections (only on first page, no filters)
        $seoSections = [];
        if ($request->input('page', 1) == 1 && !$request->hasAny(['search', 'tags', 'gender', 'platform'])) {
            $seoSections = $this->getSeoSections();
        }

        // User's online favorites
        $onlineFavorites = collect();
        if (auth()->check() && $request->input('page', 1) == 1) {
            $onlineFavorites = auth()->user()->onlineFavorites()->limit(8)->get();
        }

        // Country-based suggestion section (first page, no filters)
        $countryModels = collect();
        $visitorCountry = null;
        if ($request->input('page', 1) == 1 && !$request->hasAny(['search', 'tags', 'gender', 'platform', 'country'])) {
            [$countryModels, $visitorCountry] = $this->getCountryModels($request);
        }

        // SEO schemas (page 1 only, no filters)
        $seoSchemas = [];
        if ($request->input('page', 1) == 1 && !$request->hasAny(['search', 'tags', 'gender', 'platform'])) {
            $seoSchemas = $this->seoService->getHomepageSchema($totalCount, $onlineCount);

            $seoSchemas[] = [
                '@context' => 'https://schema.org',
                '@type' => 'ItemList',
                'name' => 'Live Cam Models',
                'numberOfItems' => $totalCount,
                'itemListElement' => $models->getCollection()->map(fn($m, $i) => [
                    '@type' => 'ListItem',
                    'position' => $i + 1,
                    'url' => $m->url,
                    'name' => $m->username,
                ])->values()->toArray(),
            ];
        }

        return view('cam-models.index', [
            'models' => $models,
            'platforms' => $platforms,
            'genders' => $genders,
            'totalCount' => $totalCount,
            'onlineCount' => $onlineCount,
            'filters' => $request->only(['online', 'platform', 'gender', 'age_min', 'age_max', 'hd', 'search', 'tags', 'sort', 'direction']),
            'seoSections' => $seoSections,
            'seoSchemas' => $seoSchemas,
            'onlineFavorites' => $onlineFavorites,
            'countryModels' => $countryModels,
            'visitorCountry' => $visitorCountry,
        ]);
    }

    /**
     * Get SEO-optimized featured sections
     */
    private function getSeoSections(): array
    {
        return cache()->remember('cam:seo_sections:' . app()->getLocale(), 120, function () {
            $sections = [];
            $dbSections = HomepageSection::getActiveSections();

            foreach ($dbSections as $section) {
                $tags = $section->tags ?? [];
                if (empty($tags)) {
                    continue;
                }

                $models = CamModel::where('is_online', true)
                    ->where(function ($q) use ($tags) {
                        foreach ($tags as $tag) {
                            $q->orWhereJsonContains('tags', $tag);
                        }
                    })
                    ->orderByRaw("CASE WHEN source_platform = 'chaturbate' THEN 1 ELSE 0 END ASC")
                    ->orderBy('viewers_count', 'desc')
                    ->limit($section->max_models)
                    ->get();

                if ($models->count() >= $section->min_models) {
                    $sections[] = [
                        'title' => $section->localized_title,
                        'slug' => $section->slug,
                        'models' => $models,
                    ];
                }
            }

            return $sections;
        });
    }

    /**
     * API endpoint for infinite scroll
     */
    public function loadMore(Request $request)
    {
        $perPage = 48;
        $page = (int) $request->input('page', 1);
        $hasFilters = $request->hasAny(['search', 'tags', 'gender', 'platform', 'country', 'age_min', 'age_max', 'hd', 'online', 'niche', 'niche_tag']);
        $isDefaultSort = !$request->filled('sort') || $request->input('sort') === 'viewers_count';

        if (!$hasFilters && $isDefaultSort) {
            $models = $this->getHomepageModels($perPage, $page);
        } else {
            $query = CamModel::query();
            $this->applyFilters($request, $query);
            $models = $query->simplePaginate($perPage, ['*'], 'page', $page);
        }

        // Render model cards HTML
        $html = '';
        foreach ($models as $model) {
            $html .= view('components.pornguru.model-card', ['model' => $model])->render();
        }

        return response()->json([
            'html' => $html,
            'hasMore' => $models->hasMorePages(),
            'nextPage' => $models->currentPage() + 1,
        ]);
    }

    /**
     * API endpoint for live goal data refresh
     */
    public function goalData(CamModel $model)
    {
        // Refresh model from DB to get latest goal data
        $model->refresh();

        return response()->json([
            'goal_message' => $model->goal_message,
            'goal_needed' => $model->goal_needed,
            'goal_earned' => $model->goal_earned,
            'goal_progress' => $model->goal_progress,
            'is_online' => $model->is_online,
            'viewers_count' => $model->viewers_count,
        ]);
    }

    /**
     * Display a single cam model
     */
    public function show(CamModel $model)
    {
        // Get similar models (same gender, online first)
        $similarModels = CamModel::where('id', '!=', $model->id)
            ->when($model->gender, function ($q) use ($model) {
                $q->where('gender', $model->gender);
            })
            ->orderBy('is_online', 'desc')
            ->orderByRaw("CASE WHEN source_platform = 'chaturbate' THEN 1 ELSE 0 END ASC")
            ->orderBy('viewers_count', 'desc')
            ->limit(12)
            ->get();

        // Get next/previous online models sorted by viewers (same order as homepage)
        // "Next" = the model with fewer viewers (lower in the list)
        $nextModel = CamModel::where('is_online', true)
            ->where('id', '!=', $model->id)
            ->where(function ($q) use ($model) {
                $q->where('viewers_count', '<', $model->viewers_count)
                    ->orWhere(function ($q2) use ($model) {
                        $q2->where('viewers_count', $model->viewers_count)
                            ->where('id', '<', $model->id);
                    });
            })
            ->orderByRaw("CASE WHEN source_platform = 'chaturbate' THEN 1 ELSE 0 END ASC")
            ->orderBy('viewers_count', 'desc')
            ->first();

        // "Prev" = the model with more viewers (higher in the list)
        $prevModel = CamModel::where('is_online', true)
            ->where('id', '!=', $model->id)
            ->where(function ($q) use ($model) {
                $q->where('viewers_count', '>', $model->viewers_count)
                    ->orWhere(function ($q2) use ($model) {
                        $q2->where('viewers_count', $model->viewers_count)
                            ->where('id', '>', $model->id);
                    });
            })
            ->orderByRaw("CASE WHEN source_platform = 'chaturbate' THEN 1 ELSE 0 END ASC")
            ->orderBy('viewers_count', 'asc')
            ->first();

        // Wrap around if at either end of the list
        if (! $nextModel) {
            $nextModel = CamModel::where('is_online', true)
                ->where('id', '!=', $model->id)
                ->orderByRaw("CASE WHEN source_platform = 'chaturbate' THEN 1 ELSE 0 END ASC")
                ->orderBy('viewers_count', 'desc')
                ->first();
        }
        if (! $prevModel) {
            $prevModel = CamModel::where('is_online', true)
                ->where('id', '!=', $model->id)
                ->orderByRaw("CASE WHEN source_platform = 'chaturbate' THEN 1 ELSE 0 END ASC")
                ->orderBy('viewers_count', 'asc')
                ->first();
        }

        // Get SEO data: description, FAQs, schemas
        $modelDescription = ModelDescription::getForModel($model->username);
        $modelFaqs = ModelFaq::forModel($model->id);
        $seoSchemas = $this->seoService->getModelSchema($model);

        $platformName = match ($model->source_platform) {
            'stripchat' => 'Stripchat',
            'chaturbate' => 'Chaturbate',
            'bongacams' => 'BongaCams',
            'xlovecam' => 'XloveCam',
            default => ucfirst($model->source_platform ?? 'Cam'),
        };

        $topTags = array_slice($model->tags ?? [], 0, 5);
        $tagHashtags = implode(' ', array_map(
            fn($t) => '#' . str_replace(['-', '_', ' '], '', $t),
            $topTags
        ));

        $metaTitle = $this->buildModelMetaTitle($model, $platformName);

        $agePart = $model->age ? "{$model->age}yo" : '';
        $countryPart = $model->country ? "from {$model->country}" : '';
        $demoParts = implode(' ', array_filter([$agePart, $countryPart]));

        $seoVars = [
            'username' => $model->username,
            'platform' => $platformName,
            'demo'     => $demoParts,
            'tags'     => $tagHashtags,
        ];

        if (!empty($modelDescription['short_description'])) {
            $metaDescription = __('seo.model_desc_ai', array_merge($seoVars, [
                'description' => $modelDescription['short_description'],
            ]));
        } else {
            $metaDescription = __('seo.model_desc', $seoVars);
        }

        $metaDescription = \Illuminate\Support\Str::limit($metaDescription, 160, '');

        $hreflangUrls = $this->buildModelHreflangUrls($model);

        return view('cam-models.show', [
            'model' => $model,
            'similarModels' => $similarModels,
            'nextModel' => $nextModel,
            'prevModel' => $prevModel,
            'modelDescription' => $modelDescription,
            'modelFaqs' => $modelFaqs,
            'seoSchemas' => $seoSchemas,
            'metaTitle' => $metaTitle,
            'metaDescription' => $metaDescription,
            'hreflangUrls' => $hreflangUrls,
            'platformName' => $platformName,
        ]);
    }

    /**
     * Get online models from the visitor's country (via Cloudflare CF-IPCountry header).
     *
     * @return array{0: \Illuminate\Support\Collection, 1: array|null}
     */
    private function getCountryModels(Request $request): array
    {
        $countryCode = strtoupper((string) $request->header('CF-IPCountry', ''));

        if (!$countryCode || $countryCode === 'XX' || $countryCode === 'T1') {
            return [collect(), null];
        }

        $country = \App\Models\Country::where('code', $countryCode)->first();

        if (!$country) {
            return [collect(), null];
        }

        $models = CamModel::where('is_online', true)
            ->where('gender', 'female')
            ->where(function ($q) use ($country) {
                $q->where('country', $country->name)
                  ->orWhere('country', $country->code);
            })
            ->orderByRaw("CASE WHEN source_platform = 'chaturbate' THEN 1 ELSE 0 END ASC")
            ->orderBy('viewers_count', 'desc')
            ->limit(8)
            ->get();

        if ($models->count() < 3) {
            return [collect(), null];
        }

        return [$models, [
            'name' => $country->localized_name ?? $country->name,
            'slug' => $country->slug,
            'flag' => country_flag($country->code),
        ]];
    }

    /**
     * Full-screen TikTok-style explore feed (mobile-first, SEO-friendly).
     */
    public function explore(Request $request, ?string $category = null)
    {
        $validCategories = ['girls', 'couples', 'men', 'trans'];
        if ($category && !in_array($category, $validCategories)) {
            abort(404);
        }

        $perPage = 12;

        $query = CamModel::where('is_online', true)
            ->where(function ($q) {
                $q->whereNotNull('stream_url')->where('stream_url', '!=', '')
                  ->orWhereNotNull('stream_urls');
            });

        if ($category) {
            $query->inNiche($category);
        }

        $query->orderByRaw("CASE WHEN source_platform = 'chaturbate' THEN 1 ELSE 0 END ASC")
            ->orderBy('viewers_count', 'desc');

        $models = $query->paginate($perPage)->withQueryString();

        $categoryLabels = [
            null => __('models.all_live_cams'),
            'girls' => __('models.live_girls'),
            'couples' => __('models.live_couples'),
            'men' => __('models.live_men'),
            'trans' => __('models.live_trans'),
        ];

        $pageTitle = ($categoryLabels[$category] ?? $categoryLabels[null])
            . ($models->currentPage() > 1 ? ' - ' . __('pagination.page') . ' ' . $models->currentPage() : '');

        $categoryUrls = [];
        foreach ([null, ...$validCategories] as $cat) {
            $categoryUrls[$cat ?? 'all'] = localized_route('explore', $cat ? ['category' => $cat] : []);
        }

        $hreflangUrls = $this->buildExploreHreflangUrls($category);

        return view('cam-models.explore', [
            'models' => $models,
            'category' => $category,
            'categoryLabels' => $categoryLabels,
            'categoryUrls' => $categoryUrls,
            'pageTitle' => $pageTitle,
            'hreflangUrls' => $hreflangUrls,
        ]);
    }

    /**
     * API endpoint for explore feed — returns next batch of models as JSON.
     */
    public function exploreApi(Request $request)
    {
        $offset = (int) $request->input('offset', 0);
        $limit = min((int) $request->input('limit', 6), 20);
        $exclude = $request->input('exclude', []);
        $category = $request->input('category');

        $query = CamModel::where('is_online', true)
            ->where(function ($q) {
                $q->whereNotNull('stream_url')->where('stream_url', '!=', '')
                  ->orWhereNotNull('stream_urls');
            });

        if ($category && in_array($category, ['girls', 'couples', 'men', 'trans'])) {
            $query->inNiche($category);
        }

        $query->orderByRaw("CASE WHEN source_platform = 'chaturbate' THEN 1 ELSE 0 END ASC")
            ->orderBy('viewers_count', 'desc');

        if (!empty($exclude)) {
            $query->whereNotIn('id', array_slice((array) $exclude, 0, 200));
        }

        $models = $query->offset($offset)->limit($limit)->get();

        // Batch-load tip menus for all models at once (avoid N+1)
        $usernames = $models->pluck('username')->all();
        $tipMenusByModel = \App\Models\ModelTipMenuItem::whereIn('model_id', $usernames)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('token_price')
            ->get()
            ->groupBy('model_id');

        return response()->json([
            'models' => $models->map(fn (CamModel $m) => [
                'id' => $m->id,
                'username' => $m->username,
                'age' => $m->age,
                'country' => $m->country,
                'gender' => $m->gender,
                'viewers_count' => $m->viewers_count,
                'stream_url' => $m->best_stream_url,
                'image_url' => $m->best_image_url,
                'affiliate_url' => $m->affiliate_url,
                'platform' => $m->source_platform,
                'url' => $m->url,
                'flag' => $m->country ? country_flag($m->country) : null,
                'stream_title' => $m->stream_title,
                'is_hd' => $m->is_hd,
                'rating' => $m->rating,
                'tags' => array_slice($m->tags ?? [], 0, 8),
                'description' => $m->description ? \Illuminate\Support\Str::limit($m->description, 200) : null,
                'languages' => $m->languages,
                'goal_message' => $m->goal_message,
                'goal_needed' => $m->goal_needed,
                'goal_earned' => $m->goal_earned,
                'goal_progress' => $m->goal_progress,
                'tip_menu' => ($tipMenusByModel[$m->username] ?? collect())
                    ->take(6)
                    ->map(fn ($item) => [
                        'emoji' => $item->emoji,
                        'name' => $item->translated_name,
                        'price' => $item->token_price,
                    ])->values(),
            ]),
            'hasMore' => $models->count() === $limit,
        ]);
    }

    /**
     * Build a translated meta title that stays within the safe display width.
     * CJK locales (ja/ko/zh) get a lower char limit since full-width chars
     * are ~2x the pixel width of Latin characters.
     */
    private function buildModelMetaTitle(CamModel $model, string $platformName): string
    {
        $vars = ['username' => $model->username, 'platform' => $platformName];
        $maxLen = in_array(app()->getLocale(), ['ja', 'ko', 'zh']) ? 40 : 60;

        $tiers = ['model_title_full', 'model_title_medium', 'model_title_short', 'model_title_mini'];

        foreach ($tiers as $tier) {
            $title = __("seo.{$tier}", $vars);
            if (mb_strlen($title) <= $maxLen) {
                return $title;
            }
        }

        return __('seo.model_title_mini', $vars);
    }

    /**
     * Default homepage query using UNION to leverage the (is_online, viewers_count) index.
     * Non-chaturbate (HLS-capable) models come first, then chaturbate, both sorted by
     * is_online DESC, viewers_count DESC. Avoids the CASE expression that forces a seq scan.
     */
    private function getHomepageModels(int $perPage, int $page): Paginator
    {
        $offset = ($page - 1) * $perPage;
        $fetch = $perPage + 1; // +1 to detect hasMorePages
        $innerLimit = $offset + $fetch;
        $table = (new CamModel)->getTable();
        $conn = (new CamModel)->getConnectionName();

        $rows = DB::connection($conn)->select("
            SELECT * FROM (
                (SELECT * FROM {$table} WHERE source_platform != 'chaturbate'
                 ORDER BY is_online DESC, viewers_count DESC NULLS LAST LIMIT ?)
                UNION ALL
                (SELECT * FROM {$table} WHERE source_platform = 'chaturbate'
                 ORDER BY is_online DESC, viewers_count DESC NULLS LAST LIMIT ?)
            ) sub
            LIMIT ? OFFSET ?
        ", [$innerLimit, $innerLimit, $fetch, $offset]);

        $items = collect($rows)->map(fn ($r) => (new CamModel)->newFromBuilder((array) $r));
        $hasMore = $items->count() > $perPage;

        return new Paginator($items->take($perPage), $perPage, $page, [
            'path' => Paginator::resolveCurrentPath(),
        ]);
    }

    /**
     * Cam Roulette — random model matching, targets chatroulette/cam roulette keywords.
     */
    public function roulette(Request $request, ?string $category = null)
    {
        $validCategories = ['girls', 'couples', 'men', 'trans'];
        if ($category && !in_array($category, $validCategories)) {
            abort(404);
        }

        $model = $this->getRandomOnlineModel($category);

        $categoryLabels = [
            null => __('roulette.all_cams'),
            'girls' => __('roulette.girls'),
            'couples' => __('roulette.couples'),
            'men' => __('roulette.men'),
            'trans' => __('roulette.trans'),
        ];

        $categoryUrls = [];
        foreach ([null, ...$validCategories] as $cat) {
            $categoryUrls[$cat ?? 'all'] = localized_route('roulette', $cat ? ['category' => $cat] : []);
        }

        $pageTitle = __('roulette.title') . ($category ? ' - ' . ($categoryLabels[$category] ?? '') : '');

        $hreflangUrls = $this->buildRouletteHreflangUrls($category);

        return view('cam-models.roulette', [
            'model' => $model,
            'category' => $category,
            'categoryLabels' => $categoryLabels,
            'categoryUrls' => $categoryUrls,
            'pageTitle' => $pageTitle,
            'hreflangUrls' => $hreflangUrls,
        ]);
    }

    /**
     * API endpoint for roulette — returns a random model as JSON.
     */
    public function rouletteApi(Request $request)
    {
        $category = $request->input('category');
        $excludeId = $request->input('exclude');

        $model = $this->getRandomOnlineModel($category, $excludeId);

        if (!$model) {
            return response()->json(['model' => null]);
        }

        return response()->json([
            'model' => [
                'id' => $model->id,
                'username' => $model->username,
                'age' => $model->age,
                'country' => $model->country,
                'gender' => $model->gender,
                'viewers_count' => $model->viewers_count,
                'stream_url' => $model->best_stream_url,
                'image_url' => $model->best_image_url,
                'affiliate_url' => $model->affiliate_url,
                'platform' => $model->source_platform,
                'url' => $model->url,
                'flag' => $model->country ? country_flag($model->country) : null,
                'stream_title' => $model->stream_title,
                'is_hd' => $model->is_hd,
                'rating' => $model->rating,
                'tags' => array_slice($model->tags ?? [], 0, 8),
                'avatar_url' => $model->avatar_url,
                'is_online' => $model->is_online,
            ],
        ]);
    }

    /**
     * Pick a random online model using a cached count + random offset.
     * Avoids ORDER BY RANDOM() which requires sorting all matching rows.
     */
    private function getRandomOnlineModel(?string $category = null, ?string $excludeId = null): ?CamModel
    {
        $cacheKey = 'roulette:count' . ($category ? ":{$category}" : '');

        $count = cache()->remember($cacheKey, 60, function () use ($category) {
            $q = CamModel::where('is_online', true)
                ->where(function ($q) {
                    $q->whereNotNull('stream_url')->where('stream_url', '!=', '')
                      ->orWhereNotNull('stream_urls');
                });
            if ($category) {
                $q->inNiche($category);
            }
            return $q->count();
        });

        if ($count === 0) {
            return null;
        }

        $query = CamModel::where('is_online', true)
            ->where(function ($q) {
                $q->whereNotNull('stream_url')->where('stream_url', '!=', '')
                  ->orWhereNotNull('stream_urls');
            });

        if ($category) {
            $query->inNiche($category);
        }

        $offset = random_int(0, max(0, $count - 1));
        $model = (clone $query)->orderByDesc('viewers_count')
            ->offset($offset)->limit(1)->first();

        if ($model && $excludeId && $model->id === $excludeId) {
            $nextOffset = ($offset + 1) % $count;
            $model = (clone $query)->orderByDesc('viewers_count')
                ->offset($nextOffset)->limit(1)->first();
        }

        return $model;
    }

    private function buildRouletteHreflangUrls(?string $category): array
    {
        $urls = [];
        $priorityLocales = config('locales.priority', ['en', 'es', 'fr', 'de', 'pt']);
        $params = $category ? ['category' => $category] : [];

        foreach ($priorityLocales as $locale) {
            if ($locale === 'en') {
                $urls['en'] = route('roulette', $params);
            } else {
                $path = "/{$locale}/roulette" . ($category ? "/{$category}" : '');
                $urls[$locale] = url($path);
            }
        }

        $urls['x-default'] = route('roulette', $params);
        return $urls;
    }

    private function buildExploreHreflangUrls(?string $category): array
    {
        $urls = [];
        $priorityLocales = config('locales.priority', ['en', 'es', 'fr', 'de', 'pt']);
        $params = $category ? ['category' => $category] : [];

        foreach ($priorityLocales as $locale) {
            if ($locale === 'en') {
                $urls['en'] = route('explore', $params);
            } else {
                $path = "/{$locale}/explore" . ($category ? "/{$category}" : '');
                $urls[$locale] = url($path);
            }
        }

        $urls['x-default'] = route('explore', $params);
        return $urls;
    }

    /**
     * Build hreflang URLs for a model page across all priority locales.
     * Model usernames are not translated, only the URL prefix changes.
     */
    private function buildModelHreflangUrls(CamModel $model): array
    {
        $urls = [];
        $priorityLocales = config('locales.priority', ['en', 'es', 'fr', 'de', 'pt']);

        foreach ($priorityLocales as $locale) {
            if ($locale === 'en') {
                $urls['en'] = route('cam-models.show', $model);
            } else {
                $urls[$locale] = url("/{$locale}/model/{$model->username}");
            }
        }

        // x-default points to English
        $urls['x-default'] = route('cam-models.show', $model);

        return $urls;
    }
}

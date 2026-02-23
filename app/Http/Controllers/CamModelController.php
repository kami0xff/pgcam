<?php

namespace App\Http\Controllers;

use App\Models\CamModel;
use App\Models\HomepageSection;
use App\Models\ModelDescription;
use App\Models\ModelFaq;
use App\Services\SeoService;
use Illuminate\Http\Request;

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
        $query = CamModel::query();
        $this->applyFilters($request, $query);

        // Get paginated results (larger batch for infinite scroll)
        $perPage = 48;
        $models = $query->paginate($perPage)->withQueryString();

        // Get unique values for filter dropdowns
        $platforms = CamModel::distinct()->pluck('source_platform')->filter()->sort()->values();
        $genders = CamModel::distinct()->pluck('gender')->filter()->sort()->values();

        // Stats
        $totalCount = CamModel::count();
        $onlineCount = CamModel::online()->count();

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

        return view('cam-models.index', [
            'models' => $models,
            'platforms' => $platforms,
            'genders' => $genders,
            'totalCount' => $totalCount,
            'onlineCount' => $onlineCount,
            'filters' => $request->only(['online', 'platform', 'gender', 'age_min', 'age_max', 'hd', 'search', 'tags', 'sort', 'direction']),
            'seoSections' => $seoSections,
            'onlineFavorites' => $onlineFavorites,
        ]);
    }

    /**
     * Get SEO-optimized featured sections
     */
    private function getSeoSections(): array
    {
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
                        $q->orWhereJsonContains('tags', $tag)
                            ->orWhere('username', 'ilike', '%' . $tag . '%');
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
    }

    /**
     * API endpoint for infinite scroll
     */
    public function loadMore(Request $request)
    {
        $query = CamModel::query();
        $this->applyFilters($request, $query);

        $perPage = 48;
        $page = $request->input('page', 1);

        $models = $query->paginate($perPage, ['*'], 'page', $page);

        // Render model cards HTML
        $html = '';
        foreach ($models as $model) {
            $html .= view('components.pornguru.model-card', ['model' => $model])->render();
        }

        return response()->json([
            'html' => $html,
            'hasMore' => $models->hasMorePages(),
            'nextPage' => $models->currentPage() + 1,
            'total' => $models->total(),
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

        // Get next/previous online models for navigation
        $nextModel = CamModel::where('is_online', true)
            ->where('id', '>', $model->id)
            ->orderBy('id', 'asc')
            ->first();

        $prevModel = CamModel::where('is_online', true)
            ->where('id', '<', $model->id)
            ->orderBy('id', 'desc')
            ->first();

        // If no next, wrap to first online model
        if (!$nextModel) {
            $nextModel = CamModel::where('is_online', true)
                ->where('id', '!=', $model->id)
                ->orderBy('id', 'asc')
                ->first();
        }

        // If no prev, wrap to last online model
        if (!$prevModel) {
            $prevModel = CamModel::where('is_online', true)
                ->where('id', '!=', $model->id)
                ->orderBy('id', 'desc')
                ->first();
        }

        // Get SEO data: description, FAQs, schemas
        $modelDescription = ModelDescription::getForModel($model->username);
        $modelFaqs = ModelFaq::forModel($model->id);
        $seoSchemas = $this->seoService->getModelSchema($model);

        // Build meta description from generated content or fallback
        $metaDescription = $modelDescription['short_description']
            ?? "Watch {$model->username} live on PornGuru.cam. Join the free chat and interact with this amazing model.";

        // Build hreflang URLs for all priority locales
        $hreflangUrls = $this->buildModelHreflangUrls($model);

        return view('cam-models.show', [
            'model' => $model,
            'similarModels' => $similarModels,
            'nextModel' => $nextModel,
            'prevModel' => $prevModel,
            'modelDescription' => $modelDescription,
            'modelFaqs' => $modelFaqs,
            'seoSchemas' => $seoSchemas,
            'metaDescription' => $metaDescription,
            'hreflangUrls' => $hreflangUrls,
        ]);
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

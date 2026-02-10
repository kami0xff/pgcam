<?php

namespace App\Http\Controllers;

use App\Models\CamModel;
use Illuminate\Http\Request;

class CamModelController extends Controller
{
    /**
     * Apply common filters to query
     */
    private function applyFilters(Request $request, $query)
    {
        // Filter: Online only
        if ($request->boolean('online')) {
            $query->online();
        }

        // Filter: Platform (stripchat, xlovecam)
        if ($request->filled('platform')) {
            $query->platform($request->input('platform'));
        }

        // Filter: Gender
        if ($request->filled('gender')) {
            $query->gender($request->input('gender'));
        }

        // Filter: Age range
        if ($request->filled('age_min')) {
            $query->where('age', '>=', (int) $request->input('age_min'));
        }
        if ($request->filled('age_max')) {
            $query->where('age', '<=', (int) $request->input('age_max'));
        }

        // Filter: HD only
        if ($request->boolean('hd')) {
            $query->hdOnly();
        }

        // Filter: Search by username
        if ($request->filled('search')) {
            $query->where('username', 'ilike', '%' . $request->input('search') . '%');
        }

        // Filter: Tags (array of tag slugs)
        if ($request->filled('tags')) {
            $tags = is_array($request->input('tags')) 
                ? $request->input('tags') 
                : explode(',', $request->input('tags'));
            $query->withTags($tags);
        }

        // Sorting
        $sortField = $request->input('sort', 'viewers_count');
        $sortDirection = $request->input('direction', 'desc');
        
        // Always prioritize online models
        $query->orderBy('is_online', 'desc');

        // Special handling for goal_progress - sort by closest to 100%
        if ($sortField === 'goal_progress') {
            // Only show models with active goals, sorted by closest to completion
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

        // Define SEO categories with their tags/filters
        $categories = [
            [
                'title' => 'Big Ass Sex Cams',
                'slug' => 'big-ass',
                'tags' => ['big-ass', 'bigass', 'big ass', 'booty', 'pawg'],
            ],
            [
                'title' => 'Asian Sex Cams',
                'slug' => 'asian',
                'tags' => ['asian', 'japanese', 'korean', 'chinese', 'thai', 'filipina'],
            ],
            [
                'title' => 'Latina Sex Cams',
                'slug' => 'latina',
                'tags' => ['latina', 'latin', 'colombian', 'brazilian'],
            ],
            [
                'title' => 'MILF Sex Cams',
                'slug' => 'milf',
                'tags' => ['milf', 'mature', 'mom'],
            ],
        ];

        foreach ($categories as $category) {
            $models = CamModel::where('is_online', true)
                ->where(function ($q) use ($category) {
                    foreach ($category['tags'] as $tag) {
                        $q->orWhereJsonContains('tags', $tag)
                          ->orWhere('username', 'ilike', '%' . $tag . '%');
                    }
                })
                ->orderBy('viewers_count', 'desc')
                ->limit(8)
                ->get();

            if ($models->count() >= 4) {
                $sections[] = [
                    'title' => $category['title'],
                    'slug' => $category['slug'],
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

        return view('cam-models.show', [
            'model' => $model,
            'similarModels' => $similarModels,
            'nextModel' => $nextModel,
            'prevModel' => $prevModel,
        ]);
    }
}

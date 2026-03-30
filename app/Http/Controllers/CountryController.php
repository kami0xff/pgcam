<?php

namespace App\Http\Controllers;

use App\Models\CamModel;
use App\Models\Country;
use App\Services\SeoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class CountryController extends Controller
{
    protected SeoService $seoService;

    public function __construct(SeoService $seoService)
    {
        $this->seoService = $seoService;
    }

    /**
     * List all countries
     */
    public function index(Request $request)
    {
        $countries = Country::orderBy('models_count', 'desc')
            ->orderBy('name', 'asc')
            ->get();

        // If local DB has no countries at all, pull directly from cam DB
        if ($countries->isEmpty()) {
            $countries = $this->getCountriesFromCamModels();
        }

        // If counts are stale (all zero), refresh them from the cam DB
        if ($countries->isNotEmpty() && $countries->every(fn ($c) => ($c->models_count ?? 0) === 0)) {
            $liveCounts = $this->getCountriesFromCamModels();
            if ($liveCounts->isNotEmpty()) {
                $countries = $liveCounts;
            }
        }

        $hreflangUrls = ['en' => route('countries.index'), 'x-default' => route('countries.index')];
        foreach (config('locales.priority', []) as $loc) {
            if ($loc !== 'en') {
                $hreflangUrls[$loc] = url("/{$loc}/countries");
            }
        }

        return view('countries.index', [
            'countries' => $countries,
            'hreflangUrls' => $hreflangUrls,
            'seoSchemas' => [
                $this->seoService->getBreadcrumbSchema([
                    ['name' => __('common.home'), 'url' => localized_route('home')],
                    ['name' => __('common.countries'), 'url' => localized_route('countries.index')],
                ]),
            ],
        ]);
    }

    /**
     * Get countries directly from CamModel database
     */
    protected function getCountriesFromCamModels()
    {
        try {
            $countryData = CamModel::on('cam')
                ->select('country')
                ->selectRaw('COUNT(*) as models_count')
                ->whereNotNull('country')
                ->where('country', '!=', '')
                ->groupBy('country')
                ->orderByDesc('models_count')
                ->get();

            return $countryData->map(function ($item) {
                $slug = \Illuminate\Support\Str::slug($item->country);
                return (object) [
                    'name' => $item->country,
                    'slug' => $slug,
                    'code' => $this->getCountryCode($item->country),
                    'models_count' => $item->models_count,
                    'localized_name' => $item->country,
                    'url' => localized_route('countries.show', $slug),
                ];
            });
        } catch (\Exception $e) {
            \Log::warning('Countries fallback failed: ' . $e->getMessage());
            return collect();
        }
    }

    /**
     * Get ISO country code from country name
     */
    protected function getCountryCode(string $name): string
    {
        $codes = [
            'United States' => 'US', 'USA' => 'US', 'United Kingdom' => 'GB', 'UK' => 'GB',
            'Germany' => 'DE', 'France' => 'FR', 'Spain' => 'ES', 'Italy' => 'IT',
            'Netherlands' => 'NL', 'Belgium' => 'BE', 'Poland' => 'PL', 'Ukraine' => 'UA',
            'Russia' => 'RU', 'Romania' => 'RO', 'Czech Republic' => 'CZ', 'Czechia' => 'CZ',
            'Colombia' => 'CO', 'Brazil' => 'BR', 'Argentina' => 'AR', 'Mexico' => 'MX',
            'Chile' => 'CL', 'Peru' => 'PE', 'Venezuela' => 'VE', 'Philippines' => 'PH',
            'Thailand' => 'TH', 'Japan' => 'JP', 'China' => 'CN', 'South Korea' => 'KR',
            'Korea' => 'KR', 'India' => 'IN', 'Indonesia' => 'ID', 'Australia' => 'AU',
            'Canada' => 'CA', 'Latvia' => 'LV', 'Estonia' => 'EE', 'Lithuania' => 'LT',
            'Hungary' => 'HU', 'Serbia' => 'RS', 'Croatia' => 'HR', 'Bulgaria' => 'BG',
            'Moldova' => 'MD', 'Ecuador' => 'EC', 'Portugal' => 'PT', 'Austria' => 'AT',
            'Switzerland' => 'CH', 'Sweden' => 'SE', 'Norway' => 'NO', 'Denmark' => 'DK',
            'Finland' => 'FI', 'Greece' => 'GR', 'Turkey' => 'TR', 'South Africa' => 'ZA',
        ];

        return $codes[$name] ?? strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $name), 0, 2));
    }

    /**
     * Show models from a specific country
     */
    public function show(Request $request, string $slug)
    {
        $locale = App::getLocale();
        
        // Try database first
        $country = Country::findBySlug($slug, $locale);

        // If not in DB, try to find country from CamModel
        if (!$country) {
            $country = $this->findCountryBySlug($slug);
        }

        if (!$country) {
            abort(404);
        }

        // Redirect to the canonical localized URL if the slug doesn't match
        if ($country instanceof Country) {
            $correctSlug = Country::localizeSlug($country->slug, $locale);
            if ($slug !== $correctSlug && $slug !== $country->slug) {
                return redirect(localized_route('countries.show', $correctSlug), 301);
            }
        }

        $countryName = is_object($country) && property_exists($country, 'name') 
            ? $country->name 
            : $country->name;

        $query = CamModel::where('country', $countryName);

        // Also try matching by code if available
        if (!empty($country->code)) {
            $query->orWhere('country', $country->code);
        }

        if ($request->boolean('online')) {
            $query->where('is_online', true);
        }

        $query->orderBy('is_online', 'desc')
              ->orderByRaw("CASE WHEN source_platform = 'chaturbate' THEN 1 ELSE 0 END ASC")
              ->orderBy('viewers_count', 'desc');

        $models = $query->paginate(48)->withQueryString();
        
        // Get translation if country is from DB
        $translation = method_exists($country, 'translation') 
            ? $country->translation($locale) 
            : null;

        // Generate SEO schema - either from Country model or manually
        $seoSchemas = [];
        if ($country instanceof Country) {
            $seoSchemas = $this->seoService->getCountrySchema($country);
        } else {
            // Manual schema for stdClass country
            $seoSchemas = [
                [
                    '@context' => 'https://schema.org',
                    '@type' => 'CollectionPage',
                    'name' => $country->name . ' Cams',
                    'url' => localized_route('countries.show', $country->slug),
                    'numberOfItems' => $models->total(),
                ],
                $this->seoService->getBreadcrumbSchema([
                    ['name' => __('common.home'), 'url' => localized_route('home')],
                    ['name' => __('common.countries'), 'url' => localized_route('countries.index')],
                    ['name' => $country->name, 'url' => localized_route('countries.show', $country->slug)],
                ]),
            ];
        }

        $hreflangUrls = method_exists($country, 'getHreflangUrls') 
            ? $country->getHreflangUrls() 
            : [];

        if (!empty($hreflangUrls)) {
            \Illuminate\Support\Facades\View::share('langSwitchUrls', $hreflangUrls);
        }

        return view('countries.show', [
            'country' => $country,
            'models' => $models,
            'translation' => $translation,
            'seoSchemas' => $seoSchemas,
            'hreflangUrls' => $hreflangUrls,
        ]);
    }

    /**
     * Find country from CamModel by slug
     */
    protected function findCountryBySlug(string $slug)
    {
        // Cache the full country list to avoid a GROUP BY scan on every unknown slug
        $countriesBySlug = cache()->remember('cam:countries_by_slug', 3600, function () {
            try {
                return CamModel::on('cam')
                    ->select('country')
                    ->selectRaw('COUNT(*) as models_count')
                    ->whereNotNull('country')
                    ->where('country', '!=', '')
                    ->groupBy('country')
                    ->get()
                    ->keyBy(fn ($item) => \Illuminate\Support\Str::slug($item->country));
            } catch (\Exception $e) {
                return collect();
            }
        });

        $item = $countriesBySlug[$slug] ?? null;

        if ($item) {
            return (object) [
                'name' => $item->country,
                'slug' => $slug,
                'code' => $this->getCountryCode($item->country),
                'models_count' => $item->models_count,
                'localized_name' => $item->country,
            ];
        }

        // Last resort: construct from the slug itself
        $name = ucwords(str_replace('-', ' ', $slug));
        $count = CamModel::on('cam')
            ->where('country', $name)
            ->limit(1)
            ->exists();

        if ($count) {
            return (object) [
                'name' => $name,
                'slug' => $slug,
                'code' => $this->getCountryCode($name),
                'localized_name' => $name,
            ];
        }

        return null;
    }
}

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

        return view('countries.index', [
            'countries' => $countries,
            'seoSchemas' => [
                $this->seoService->getBreadcrumbSchema([
                    ['name' => __('Home'), 'url' => localized_route('home')],
                    ['name' => __('Countries'), 'url' => localized_route('countries.index')],
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
                    ['name' => __('Home'), 'url' => localized_route('home')],
                    ['name' => __('Countries'), 'url' => localized_route('countries.index')],
                    ['name' => $country->name, 'url' => localized_route('countries.show', $country->slug)],
                ]),
            ];
        }

        return view('countries.show', [
            'country' => $country,
            'models' => $models,
            'translation' => $translation,
            'seoSchemas' => $seoSchemas,
            'hreflangUrls' => method_exists($country, 'getHreflangUrls') 
                ? $country->getHreflangUrls() 
                : [],
        ]);
    }

    /**
     * Find country from CamModel by slug
     */
    protected function findCountryBySlug(string $slug)
    {
        try {
            // Get all unique countries and find matching one
            $countries = CamModel::on('cam')
                ->select('country')
                ->selectRaw('COUNT(*) as models_count')
                ->whereNotNull('country')
                ->where('country', '!=', '')
                ->groupBy('country')
                ->get();

            foreach ($countries as $item) {
                if (\Illuminate\Support\Str::slug($item->country) === $slug) {
                    return (object) [
                        'name' => $item->country,
                        'slug' => $slug,
                        'code' => $this->getCountryCode($item->country),
                        'models_count' => $item->models_count,
                        'localized_name' => $item->country,
                    ];
                }
            }
        } catch (\Exception $e) {
            // Fallback: create a country object from the slug
            $name = ucwords(str_replace('-', ' ', $slug));
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

<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CamModelController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\TagController;
use Illuminate\Support\Facades\Route;

// Health check endpoint for Docker/load balancers
Route::get('/up', function () {
    return response('OK', 200);
});

// ==============================================
// Main Routes (English - default)
// ==============================================

// Auto-detect browser language on first visit and redirect to locale-prefixed URL
Route::middleware('detect.locale')->group(function () {
    // Home
    Route::get('/', [CamModelController::class, 'index'])->name('home');

    // Model pages
    Route::get('/model/{model}', [CamModelController::class, 'show'])->name('cam-models.show');

    // Tag pages
    Route::get('/tags', [TagController::class, 'index'])->name('tags.index');
    Route::get('/tag/{slug}', [TagController::class, 'show'])->name('tags.show');

    // Niche pages (girls, couples, men, trans)
    Route::get('/{niche}', [TagController::class, 'niche'])
        ->where('niche', 'girls|couples|men|trans')
        ->name('niche.show');
    Route::get('/{niche}/{tagSlug}', [TagController::class, 'nicheTag'])
        ->where('niche', 'girls|couples|men|trans')
        ->name('niche.tag');

    // Country pages
    Route::get('/countries', [CountryController::class, 'index'])->name('countries.index');
    Route::get('/country/{slug}', [CountryController::class, 'show'])->name('countries.show');
});

// API endpoint (no locale detection needed)
Route::get('/api/models', [CamModelController::class, 'loadMore'])->name('api.models.load');

// Sitemap Index
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');

// Static pages sitemap
Route::get('/sitemap-static.xml', [SitemapController::class, 'staticPages'])->name('sitemap.static');

// Models sitemaps (paginated, with optional locale)
Route::get('/sitemap-models-{page}.xml', [SitemapController::class, 'models'])
    ->where('page', '[0-9]+')
    ->name('sitemap.models');
Route::get('/sitemap-models-{locale}-{page}.xml', [SitemapController::class, 'models'])
    ->where(['locale' => '[a-z]{2}(-[A-Z]{2})?', 'page' => '[0-9]+'])
    ->name('sitemap.models.locale');

// Tags sitemaps (with optional locale)
Route::get('/sitemap-tags.xml', [SitemapController::class, 'tags'])->name('sitemap.tags');
Route::get('/sitemap-tags-{locale}.xml', [SitemapController::class, 'tags'])
    ->where('locale', '[a-z]{2}(-[A-Z]{2})?')
    ->name('sitemap.tags.locale');

// Countries sitemaps (with optional locale)
Route::get('/sitemap-countries.xml', [SitemapController::class, 'countries'])->name('sitemap.countries');
Route::get('/sitemap-countries-{locale}.xml', [SitemapController::class, 'countries'])
    ->where('locale', '[a-z]{2}(-[A-Z]{2})?')
    ->name('sitemap.countries.locale');

// Niches sitemaps (with optional locale)
Route::get('/sitemap-niches.xml', [SitemapController::class, 'niches'])->name('sitemap.niches');
Route::get('/sitemap-niches-{locale}.xml', [SitemapController::class, 'niches'])
    ->where('locale', '[a-z]{2}(-[A-Z]{2})?')
    ->name('sitemap.niches.locale');

// Image sitemap (for Google Images SEO)
Route::get('/sitemap-images.xml', [SitemapController::class, 'images'])->name('sitemap.images');

// ==============================================
// Localized Routes (/{locale}/...)
// ==============================================

// Build locale pattern from config (excludes 'en' as that's the default)
$supportedLocales = array_keys(config('locales.supported', []));
$localePattern = implode('|', array_filter($supportedLocales, fn($l) => $l !== 'en'));

Route::prefix('{locale}')
    ->where(['locale' => $localePattern])
    ->middleware('set.locale')
    ->group(function () {
        // Localized home
        Route::get('/', [CamModelController::class, 'index'])->name('home.localized');
        
        // Localized model pages
        Route::get('/model/{model}', [CamModelController::class, 'show'])->name('cam-models.show.localized');
        
        // Localized tag pages
        Route::get('/tags', [TagController::class, 'index'])->name('tags.index.localized');
        Route::get('/tag/{slug}', [TagController::class, 'show'])->name('tags.show.localized');
        
        // Localized niche pages
        Route::get('/{niche}', [TagController::class, 'niche'])
            ->where('niche', 'girls|couples|men|trans')
            ->name('niche.show.localized');
        Route::get('/{niche}/{tagSlug}', [TagController::class, 'nicheTag'])
            ->where('niche', 'girls|couples|men|trans')
            ->name('niche.tag.localized');
        
        // Localized country pages
        Route::get('/countries', [CountryController::class, 'index'])->name('countries.index.localized');
        Route::get('/country/{slug}', [CountryController::class, 'show'])->name('countries.show.localized');
    });

// ==============================================
// Authentication
// ==============================================

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [AuthController::class, 'dashboard'])->name('dashboard');
});

// ==============================================
// API Routes
// ==============================================

Route::get('/api/favorites', [FavoriteController::class, 'index'])->name('api.favorites');
Route::post('/api/favorite/{model}', [FavoriteController::class, 'toggle'])->name('api.favorite.toggle');

// Model goal polling (live refresh)
Route::get('/api/model/{model}/goal', [CamModelController::class, 'goalData'])->name('api.model.goal');

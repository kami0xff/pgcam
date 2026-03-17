<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CamModelController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\PrelanderApiController;
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

    // Niche p ages (girls, couples, men, trans)
    Route::get('/{niche}', [TagController::class, 'niche'])
        ->where('niche', 'girls|couples|men|trans')
        ->name('niche.show');
    Route::get('/{niche}/{tagSlug}', [TagController::class, 'nicheTag'])
        ->where('niche', 'girls|couples|men|trans')
        ->name('niche.tag');

    // Country pages
    Route::get('/countries', [CountryController::class, 'index'])->name('countries.index');
    Route::get('/country/{slug}', [CountryController::class, 'show'])->name('countries.show');

    // Explore (TikTok-style swipe feed)
    Route::get('/explore/{category?}', [CamModelController::class, 'explore'])
        ->where('category', 'girls|couples|men|trans')
        ->name('explore');

    // Cam Roulette (random model matching)
    Route::get('/roulette/{category?}', [CamModelController::class, 'roulette'])
        ->where('category', 'girls|couples|men|trans')
        ->name('roulette');
});

// API endpoint (no locale detection needed)
Route::get('/api/models', [CamModelController::class, 'loadMore'])->name('api.models.load');
Route::get('/api/explore', [CamModelController::class, 'exploreApi'])->name('api.explore');
Route::get('/api/roulette', [CamModelController::class, 'rouletteApi'])->name('api.roulette');

// Sitemaps — strip session/cookie/CSRF middleware so Cloudflare can cache at the edge
Route::withoutMiddleware([
    \Illuminate\Session\Middleware\StartSession::class,
    \Illuminate\View\Middleware\ShareErrorsFromSession::class,
    \Illuminate\Cookie\Middleware\EncryptCookies::class,
    \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
    \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
])->group(function () {
    Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');

    // 410 Gone for all old sub-sitemaps so crawlers stop requesting them
    Route::get('/sitemap-{any}.xml', fn () => response('', 410))
        ->where('any', '.*');
});

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

        // Localized explore
        Route::get('/explore/{category?}', [CamModelController::class, 'explore'])
            ->where('category', 'girls|couples|men|trans')
            ->name('explore.localized');

        // Localized roulette
        Route::get('/roulette/{category?}', [CamModelController::class, 'roulette'])
            ->where('category', 'girls|couples|men|trans')
            ->name('roulette.localized');

        // Localized legal/static pages
        Route::get('/about', fn() => view('legal.about'))->name('about.localized');
        Route::get('/contact', fn() => view('legal.contact'))->name('contact.localized');
        Route::get('/privacy', fn() => view('legal.privacy'))->name('privacy.localized');
        Route::get('/terms', fn() => view('legal.terms'))->name('terms.localized');
        Route::get('/dmca', fn() => view('legal.dmca'))->name('dmca.localized');
        Route::get('/2257', fn() => view('legal.2257'))->name('2257.localized');
        Route::get('/good-causes', fn() => view('legal.support'))->name('good-causes.localized');
        Route::get('/faq', fn() => view('legal.faq'))->name('faq.localized');

        // Redirects for broken URL patterns
        Route::get('/country', fn() => redirect('/' . app()->getLocale() . '/countries', 301));
        Route::get('/guys/{tagSlug?}', fn(?string $tagSlug = null) =>
            redirect($tagSlug ? '/' . app()->getLocale() . "/men/{$tagSlug}" : '/' . app()->getLocale() . '/men', 301));
    });

// ==============================================
// Redirects (fix old/broken URLs found by crawlers)
// ==============================================

Route::get('/country', fn() => redirect('/countries', 301));
Route::get('/guys/{tagSlug?}', fn(?string $tagSlug = null) =>
    redirect($tagSlug ? "/men/{$tagSlug}" : '/men', 301));

// ==============================================
// Legal & Static Pages
// ==============================================

Route::get('/about', fn() => view('legal.about'))->name('about');
Route::get('/contact', fn() => view('legal.contact'))->name('contact');
Route::get('/privacy', fn() => view('legal.privacy'))->name('privacy');
Route::get('/terms', fn() => view('legal.terms'))->name('terms');
Route::get('/dmca', fn() => view('legal.dmca'))->name('dmca');
Route::get('/2257', fn() => view('legal.2257'))->name('2257');
Route::get('/good-causes', fn() => view('legal.support'))->name('good-causes');
Route::get('/faq', fn() => view('legal.faq'))->name('faq');

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

// ==============================================
// Prelander Public API (CORS-enabled, cached)
// ==============================================

Route::prefix('api/v1/cam-models')->group(function () {
    Route::get('/online', [PrelanderApiController::class, 'online']);
    Route::get('/near-goal', [PrelanderApiController::class, 'nearGoal']);
    Route::get('/stats', [PrelanderApiController::class, 'stats']);
});

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Detects the user's preferred language from their browser Accept-Language header
 * and redirects them to the locale-prefixed version of the URL.
 *
 * This middleware runs on the default (English / non-prefixed) routes. If the user's
 * browser prefers a supported non-English locale, they are redirected once. A cookie
 * remembers the detection so it only fires on the very first visit.
 */
class DetectLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        // Skip if this is an API, AJAX, bot, or asset request
        if (
            $request->expectsJson() ||
            $request->ajax() ||
            $request->is('api/*') ||
            $request->is('sitemap*') ||
            $request->is('up')
        ) {
            return $next($request);
        }

        // Skip if the user already has a locale cookie (they've been detected or chose manually)
        if ($request->cookie('locale_detected')) {
            return $next($request);
        }

        // Detect the preferred locale from the Accept-Language header
        $preferredLocale = $this->detectBrowserLocale($request);

        // Build the response — always set the cookie so we don't re-detect
        if ($preferredLocale && $preferredLocale !== 'en') {
            // Redirect to the locale-prefixed version of the current URL
            $path = $request->path();
            $query = $request->getQueryString();
            $redirectUrl = url('/' . $preferredLocale . '/' . ($path === '/' ? '' : $path));
            if ($query) {
                $redirectUrl .= '?' . $query;
            }

            return redirect($redirectUrl)
                ->cookie('locale_detected', $preferredLocale, 60 * 24 * 365); // 1 year
        }

        // English (or unsupported language) — proceed normally, set cookie
        $response = $next($request);

        // Attach cookie to the response
        return $response->cookie('locale_detected', 'en', 60 * 24 * 365);
    }

    /**
     * Parse the Accept-Language header and return the best matching supported locale.
     */
    protected function detectBrowserLocale(Request $request): ?string
    {
        $acceptLanguage = $request->header('Accept-Language');
        if (empty($acceptLanguage)) {
            return null;
        }

        $supportedLocales = array_keys(config('locales.supported', []));

        // Parse Accept-Language header into [locale => quality] pairs
        // Example: "es-MX,es;q=0.9,en-US;q=0.8,en;q=0.7"
        $preferred = [];
        foreach (explode(',', $acceptLanguage) as $part) {
            $part = trim($part);
            if (empty($part)) continue;

            $segments = explode(';', $part);
            $locale = trim($segments[0]);
            $quality = 1.0;

            if (isset($segments[1])) {
                $qPart = trim($segments[1]);
                if (str_starts_with($qPart, 'q=')) {
                    $quality = (float) substr($qPart, 2);
                }
            }

            $preferred[$locale] = $quality;
        }

        // Sort by quality descending
        arsort($preferred);

        // Try to match against supported locales
        foreach ($preferred as $browserLocale => $quality) {
            // Normalize: browser sends "es-MX", our config uses "es-MX" or "es"
            $normalized = str_replace('_', '-', $browserLocale);

            // Exact match (e.g., "pt-BR" matches "pt-BR")
            if (in_array($normalized, $supportedLocales)) {
                return $normalized;
            }

            // Base language match (e.g., "es-AR" matches "es")
            $base = explode('-', $normalized)[0];
            if (in_array($base, $supportedLocales)) {
                return $base;
            }
        }

        return null;
    }
}

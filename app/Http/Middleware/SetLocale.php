<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->route('locale');

        // Get supported locales from config
        $supportedLocales = array_keys(config('locales.supported', ['en' => []]));

        if ($locale && in_array($locale, $supportedLocales)) {
            App::setLocale($locale);
            
            // Set text direction for RTL languages
            $rtlLocales = config('locales.rtl', []);
            if (in_array($locale, $rtlLocales)) {
                $request->attributes->set('text_direction', 'rtl');
            }

            // Remove locale from route parameters so controllers don't receive it
            // as a positional argument (fixes {locale}/model/{model} type errors)
            $request->route()->forgetParameter('locale');
        }

        $response = $next($request);

        // Update the locale_detected cookie so browser detection doesn't override
        // the user's explicit locale choice (via URL prefix)
        if ($locale && in_array($locale, $supportedLocales)) {
            $response->cookie('locale_detected', $locale, 60 * 24 * 365); // 1 year
        }

        return $response;
    }
}

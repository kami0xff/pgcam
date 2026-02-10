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
        }

        return $next($request);
    }
}

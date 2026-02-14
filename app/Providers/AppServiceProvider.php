<?php

namespace App\Providers;

use App\Models\CamModel;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Explicit route model binding for CamModel (on external 'cam' connection).
        // Implicit binding fails in localized route groups ({locale}/model/{model}).
        Route::bind('model', function (string $value) {
            return CamModel::where('username', $value)->firstOrFail();
        });
    }
}

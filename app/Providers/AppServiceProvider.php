<?php

namespace App\Providers;


use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Dedoc\Scramble\Scramble;
use Illuminate\Routing\Route;
use Dedoc\Scramble\Support\Generator\OpenApi;
use Dedoc\Scramble\Support\Generator\SecurityScheme;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

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
        Scramble::routes(function (Route $route) {
            return Str::startsWith($route->uri, 'api/');
        });

        Gate::define('viewApiDocs', function ($user) {
            Log::info('viewApiDocs gate called for user: ' . $user->email);

            // Allow list from env (comma separated), fallback to default array
            $allowedList = array_filter(array_map('trim', explode(',', (string) env('API_DOCS_ALLOWED_EMAILS', 'septiawanajipradana@gmail.com'))));
            $allowed = in_array($user->email, $allowedList, true);

            Log::info('Access ' . ($allowed ? 'granted' : 'denied') . ' for ' . $user->email);

            return $allowed;
        });

        Scramble::afterOpenApiGenerated(function (OpenApi $openApi) {
            $openApi->secure(
                SecurityScheme::http('bearer')
            );
        });
    }
}

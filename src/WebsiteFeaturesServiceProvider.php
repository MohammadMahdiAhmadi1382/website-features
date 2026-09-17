<?php

declare(strict_types=1);

namespace Codav\WebsiteFeatures;

use Illuminate\Support\ServiceProvider;

class WebsiteFeaturesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/website-features.php',
            'website-features'
        );
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(
            __DIR__ . '/../routes/api.php'
        );

        $this->loadMigrationsFrom(
            __DIR__ . '/../database/migrations'
        );

        $this->publishes([
            __DIR__ . '/../config/website-features.php' => config_path('website-features.php'),
        ], 'website-features-config');

        $this->publishes([
            __DIR__ . '/../database/migrations' => database_path('migrations'),
        ], 'website-features-migrations');

        $this->publishes([
            __DIR__ . '/../routes/api.php' => base_path('routes/website-features.php'),
        ], 'website-features-routes');
    }
}
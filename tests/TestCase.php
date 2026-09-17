<?php

declare(strict_types=1);

namespace Codav\WebsiteFeatures\Tests;

use Codav\WebsiteFeatures\WebsiteFeaturesServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    use RefreshDatabase;

    protected function getPackageProviders($app): array
    {
        return [
            WebsiteFeaturesServiceProvider::class,
        ];
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(
            __DIR__ . '/../database/migrations'
        );
    }
}
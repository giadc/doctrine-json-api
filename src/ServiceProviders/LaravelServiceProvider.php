<?php

namespace Giadc\DoctrineJsonApi\ServiceProviders;

use Illuminate\Support\ServiceProvider;

class LaravelServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
    }

    /**
     * Register the application services.
     */
    public function register(): void
    {
        $this->app->bind(
            'Giadc\DoctrineJsonApi\Interfaces\AbstractJsonApiRepositoryInterface',
            'Giadc\DoctrineJsonApi\Repositories\AbstractJsonApiDoctrineRepository'
        );
    }
}

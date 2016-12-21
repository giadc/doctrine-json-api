<?php
namespace Giadc\DoctrineJsonApi\ServiceProviders;

use Illuminate\Support\ServiceProvider;

class LaravelServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            'Giadc\DoctrineJsonApi\Interfaces\AbstractJsonApiRepositoryInterface',
            'Giadc\DoctrineJsonApi\Repositories\AbstractJsonApiDoctrineRepository'
        );
    }
}

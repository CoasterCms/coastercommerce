<?php

namespace CoasterCommerce\Core\Providers;

use Illuminate\Routing\Router;
use Illuminate\Config\Repository;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class RoutesProvider extends BaseServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @param Router $router
     * @param Repository $config
     * @return void
     */
    public function boot(Router $router, Repository $config)
    {
        $router->middleware(['web'])
            ->as('coaster-commerce.frontend.')
            ->namespace('CoasterCommerce\Core\Controllers\Frontend')
            ->group(coaster_commerce_base_path('routes/frontend.php'));

        // using cms admin auth currently so requires web middleware (sessions)
        $router->middleware(['web', 'api'])
        ->as('coaster-commerce.api.')
            ->prefix($config->get('coaster-commerce.url.api'))
            ->namespace('CoasterCommerce\Core\Controllers\Api')
            ->group(coaster_commerce_base_path('routes/api.php'));

        // must be last as it has a catch all route
        $router->middleware(['web', 'coaster-commerce.admin'])
            ->as('coaster-commerce.admin.')
            ->prefix($config->get('coaster-commerce.url.admin'))
            ->namespace('CoasterCommerce\Core\Controllers\Admin')
            ->group(coaster_commerce_base_path('routes/admin.php'));
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {

    }
}

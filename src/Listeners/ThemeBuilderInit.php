<?php

namespace CoasterCommerce\Core\Listeners;

use CoasterCms\Events\Admin\ThemeBuilderInit as ThemeBuilderInitEvent;
use CoasterCommerce\Core\Events\FrontendInit;

class ThemeBuilderInit
{

    /**
     * @param ThemeBuilderInitEvent $event
     */
    public function handle(ThemeBuilderInitEvent $event)
    {
        event(new FrontendInit(app('coaster-commerce.url-resolver')));
        // default vars for search page (stops errors in cms themebuilder)
        $searchData = new \stdClass();
        $searchData->term = null;
        $searchData->results = null;
        view()->share('catalogueSearch', $searchData);
        view()->share('products', []);
    }

}


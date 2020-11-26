<?php

namespace CoasterCommerce\Core\Listeners;

use CoasterCommerce\Core\Events\FrontendInit as FrontendInitEvent;
use CoasterCommerce\Core\Model\Category;
use CoasterCommerce\Core\Model\Product;

class FrontendInit
{

    /**
     * @param FrontendInitEvent $event
     */
    public function handle(FrontendInitEvent $event)
    {
        // load commerce crumbs global view var
        view()->share('coasterCommerceCrumbs', $event->urlResolver->loadCrumbs());
        view()->share('coasterCommerceMetas', $event->urlResolver->loadMetas());
        // product canonical url
        $product = $event->urlResolver->getProduct();
        view()->share('coasterCommerceCanonical', $product ? url($product->getUrl()) : null);
        // default vars
        view()->share('product', new Product());
        view()->share('category', new Category());
    }

}


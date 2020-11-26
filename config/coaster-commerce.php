<?php

return [

    // urls used by commerce system

    'url' => [

        'frontend' => [
            'customer' => '/customer',
            'checkout' => '/checkout',
            'search' => '/search',
            'stock-notify' => '/stock-notify',
        ],

        'admin' => '/coaster-commerce',

        'api' => '/coaster-commerce/api',

        'assets' => '/cc-assets',

    ],

    // shared variable for used in all views

    'cart' => [

        'var' => 'cart'

    ],

    // location of frontend templates (ie. account / product / checkout)

    'views' => 'coaster-commerce::frontend.theme.',

    // if category page only has one product listed redirect to product page

    'single-product-redirect' => false,

    // load pagebuilder for use in views

    'autoload-pb' => true,

];
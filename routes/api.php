<?php

use Illuminate\Routing\Router as Router;
use Illuminate\Config\Repository as Config;

/** @var Router $router */
$router = app('router');
/** @var Config $config */
$config = app('config');

// admin routes
$router->group(['middleware' => 'coaster-commerce.api:admin'], function (Router $router) {

    $router->get('/customer/admin-list', ['uses' => 'CustomerController@getAdminList', 'as' => 'customer.admin-list']);
    $router->get('/customer/carts/admin-list', ['uses' => 'AbandonedCartController@getAdminList', 'as' => 'customer.abandoned-cart.admin-list']);
    $router->get('/product/admin-list', ['uses' => 'ProductController@getAdminList', 'as' => 'product.admin-list']);
    $router->get('/attribute/admin-list', ['uses' => 'AttributeController@getAdminList', 'as' => 'attribute.admin-list']);
    $router->get('/order/admin-list', ['uses' => 'OrderController@getAdminList', 'as' => 'order.admin-list']);
    $router->get('/promotion/admin-list', ['uses' => 'PromotionController@getAdminList', 'as' => 'promotion.admin-list']);
    $router->get('/redirect/admin-list', ['uses' => 'RedirectController@getAdminList', 'as' => 'redirect.admin-list']);

    $router->get('/table-state/{name}', ['uses' => 'DatatableStateController@loadState', 'as' => 'table-state.load']);
    $router->post('/table-state/{name}', ['uses' => 'DatatableStateController@saveState', 'as' => 'table-state.save']);

});

// public routes
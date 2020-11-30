<?php

use Illuminate\Routing\Router as Router;
use Illuminate\Config\Repository as Config;

/** @var Router $router */
$router = app('router');
/** @var Config $config */
$config = app('config');

$router->get(config('coaster-commerce.url.frontend.search'), ['uses' => 'SearchController@results', 'as' => 'search.results.root']);
$router->get(config('coaster-commerce.url.frontend.search') . '/{query}', ['uses' => 'SearchController@results', 'as' => 'search.results'])
    ->where('query', '.*');

$router->post(config('coaster-commerce.url.frontend.stock-notify'), ['uses' => 'CustomerController@stockNotify', 'as' => 'stock.notify']);

$router->group(['as' => 'customer.', 'prefix' => $config->get('coaster-commerce.url.frontend.customer')], function (Router $router) use ($config) {

    $router->group(['middleware' => 'coaster-commerce.guest'], function (Router $router) {

        $router->get('login', ['uses' => 'CustomerController@login', 'as' => 'login']);
        $router->any('auth', ['uses' => 'CustomerController@auth', 'as' => 'auth']);
        $router->get('register', ['uses' => 'CustomerController@register', 'as' => 'register']);
        $router->post('register', ['uses' => 'CustomerController@createUser', 'as' => 'register.create']);
        $router->get('password-reset', ['uses' => 'CustomerController@passwordReset', 'as' => 'reset']);
        $router->post('password-reset', ['uses' => 'CustomerController@passwordResetEmail', 'as' => 'reset.email']);
        $router->get('password-update/{token}', ['uses' => 'CustomerController@passwordResetUpdate', 'as' => 'reset.update']);
        $router->post('password-update/{token}', ['uses' => 'CustomerController@passwordResetSave', 'as' => 'reset.save']);

    });

    $router->group(['middleware' => 'coaster-commerce.customer'], function (Router $router) {

        $router->get('', ['uses' => 'CustomerController@details', 'as' => 'account']);
        $router->get('password', ['uses' => 'CustomerController@passwordChange', 'as' => 'account.password']);
        $router->post('password', ['uses' => 'CustomerController@passwordUpdate', 'as' => 'account.password.update']);
        $router->get('details', ['uses' => 'CustomerController@details', 'as' => 'account.address']);
        $router->get('address/new', ['uses' => 'CustomerController@addressNew', 'as' => 'account.address.new']);
        $router->get('address/{id}/edit', ['uses' => 'CustomerController@addressEdit', 'as' => 'account.address.edit']);
        $router->post('address/{id}/save', ['uses' => 'CustomerController@addressSave', 'as' => 'account.address.save']);
        $router->get('address/{id}/delete', ['uses' => 'CustomerController@addressDelete', 'as' => 'account.address.delete']);
        $router->get('orders', ['uses' => 'CustomerController@orderList', 'as' => 'account.order.list']);
        $router->get('orders/{id}', ['uses' => 'CustomerController@orderView', 'as' => 'account.order.view']);
        $router->get('orders/{id}/reorder', ['uses' => 'CustomerController@orderReorder', 'as' => 'account.order.reorder']);
        $router->get('lists/new', ['uses' => 'WishListController@newList', 'as' => 'wishlist.new']);
        $router->post('lists/new', ['uses' => 'WishListController@saveNewList', 'as' => 'wishlist.new.save']);
        $router->get('logout', ['uses' => 'CustomerController@logout', 'as' => 'logout']);
    });

    $router->get('lists', ['uses' => 'WishListController@allLists', 'as' => 'wishlist.lists']);
    $router->get('lists/{id}/view', ['uses' => 'WishListController@viewList', 'as' => 'wishlist.view']);
    $router->post('lists/{id}/rename', ['uses' => 'WishListController@renameList', 'as' => 'wishlist.rename']);
    $router->post('lists/{id}/share', ['uses' => 'WishListController@shareList', 'as' => 'wishlist.share']);
    $router->get('lists/{id}/clear', ['uses' => 'WishListController@clearList', 'as' => 'wishlist.clear']);
    $router->get('lists/{id}/delete', ['uses' => 'WishListController@deleteList', 'as' => 'wishlist.delete']);
    $router->post('list/add-item', ['uses' => 'WishListController@addToList', 'as' => 'wishlist.add-item']);
    $router->get('list/{id}/remove-item', ['uses' => 'WishListController@removeFromList', 'as' => 'wishlist.remove-item']);

    $router->get('acart/{id}/unsubscribe', ['uses' => 'AbandonedCartController@unsubscribe', 'as' => 'abandoned-cart.unsubscribe']);
    $router->get('acart/{id}/checkout', ['uses' => 'AbandonedCartController@checkout', 'as' => 'abandoned-cart.checkout']);

});

$checkoutUrl = $config->get('coaster-commerce.url.frontend.checkout');
$router->redirect($checkoutUrl, $checkoutUrl . '/onepage');
$router->group(['as' => 'checkout.', 'prefix' => $checkoutUrl], function (Router $router) use($config) {

    $router->post('/quick-order', ['uses' => 'OrderController@quickOrder', 'as' => 'quick-order']);
    $router->get('/cart', ['uses' => 'OrderController@cart', 'as' => 'cart']);
    $router->post('/cart/add-item', ['uses' => 'OrderController@cartAdd', 'as' => 'cart.add']);
    $router->post('/cart/add-item-variation', ['uses' => 'OrderController@cartAddVariation', 'as' => 'cart.add-variation']);
    $router->get('/cart/{id}/remove-item', ['uses' => 'OrderController@cartRemove', 'as' => 'cart.remove']);
    $router->post('/cart/update', ['uses' => 'OrderController@cartUpdate', 'as' => 'cart.update']);
    $router->get('/cart/clear', ['uses' => 'OrderController@cartClear', 'as' => 'cart.clear']);
    $router->get('/cart/json', ['uses' => 'OrderController@cartJson', 'as' => 'cart.json']);
    $router->any('/onepage', ['uses' => 'OrderController@checkout', 'as' => 'onepage']);
    $router->post('/onepage/summary', ['uses' => 'OrderController@checkoutSummary', 'as' => 'onepage.summary']);
    $router->post('/onepage/check-email', ['uses' => 'OrderController@checkoutCheckEmail', 'as' => 'onepage.check-email']);
    $router->post('/onepage/save/email', ['uses' => 'OrderController@checkoutSaveEmail', 'as' => 'onepage.save.email']);
    $router->post('/onepage/save/address', ['uses' => 'OrderController@checkoutSaveAddress', 'as' => 'onepage.save.address']);
    $router->post('/onepage/save/shipping', ['uses' => 'OrderController@checkoutSaveShipping', 'as' => 'onepage.save.shipping']);
    $router->post('/onepage/save/payment', ['uses' => 'OrderController@checkoutSavePayment', 'as' => 'onepage.save.payment']);
    $router->post('/onepage/pay', ['uses' => 'OrderController@checkoutPaymentGateway', 'as' => 'onepage.pay']);
    $router->get('/callback/success/{orderKey}', ['uses' => 'OrderController@checkoutCallbackSuccess', 'as' => 'callback.success']);
    $router->get('/callback/failure/{orderKey}', ['uses' => 'OrderController@checkoutCallbackFailure', 'as' => 'callback.failure']);
    $router->get('/complete/{orderKey}', ['uses' => 'OrderController@checkoutComplete', 'as' => 'complete']);

});

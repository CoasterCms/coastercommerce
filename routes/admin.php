<?php

use Illuminate\Routing\Router as Router;

/** @var Router $router */
$router = app('router');

$router->get('order', ['uses' => 'OrderController@list', 'as' => 'order.list']);
$router->get('order/{id}/view', ['uses' => 'OrderController@view', 'as' => 'order.view']);
$router->post('order/{id}/email', ['uses' => 'OrderController@saveEmail', 'as' => 'order.save.email']);
$router->post('order/{id}/status', ['uses' => 'OrderController@saveStatus', 'as' => 'order.save.status']);
$router->post('order/{id}/shipped', ['uses' => 'OrderController@saveShipped', 'as' => 'order.save.shipped']);
$router->get('order/{id}/paid', ['uses' => 'OrderController@savePaid', 'as' => 'order.save.paid']);
$router->get('order/{id}/email-order', ['uses' => 'OrderController@emailOrder', 'as' => 'order.email.order']);
$router->post('order/{id}/add-note', ['uses' => 'OrderController@addNote', 'as' => 'order.note']);
$router->get('order/{id}/edit-address/{type}', ['uses' => 'OrderController@editAddress', 'as' => 'order.address.edit']);
$router->post('order/{id}/edit-address/{type}', ['uses' => 'OrderController@updateAddress', 'as' => 'order.address.update']);
$router->get('order/{id}/pdf', ['uses' => 'OrderController@getPdf', 'as' => 'order.pdf']);

$router->get('product', ['uses' => 'ProductController@list', 'as' => 'product.list']);
$router->get('product/add', ['uses' => 'ProductController@add', 'as' => 'product.add']);
$router->get('product/{id}/delete', ['uses' => 'ProductController@delete', 'as' => 'product.delete']);
$router->get('product/{id}/edit', ['uses' => 'ProductController@edit', 'as' => 'product.edit']);
$router->post('product/{id}/save', ['uses' => 'ProductController@save', 'as' => 'product.save']);

$router->any('product-mass-action', ['uses' => 'ProductMassActionController@index', 'as' => 'product.mass-action']);
$router->post('product-mass-action/update', ['uses' => 'ProductMassActionController@updateComplete', 'as' => 'product.mass-action.update']);
$router->post('product-mass-action/delete', ['uses' => 'ProductMassActionController@deleteComplete', 'as' => 'product.mass-action.delete']);

$router->post('product-redirect', ['uses' => 'ProductRedirectController@select', 'as' => 'product.redirect']);
$router->get('product-redirect/{id}/select', ['uses' => 'ProductRedirectController@selectSingle', 'as' => 'product.redirect.single']);
$router->post('product-redirect/apply', ['uses' => 'ProductRedirectController@apply', 'as' => 'product.redirect.apply']);
$router->get('redirect', ['uses' => 'RedirectController@list', 'as' => 'redirect.list']);
$router->get('redirect/add', ['uses' => 'RedirectController@add', 'as' => 'redirect.add']);
$router->get('redirect/{id}/delete', ['uses' => 'RedirectController@delete', 'as' => 'redirect.delete']);
$router->get('redirect/{id}/edit', ['uses' => 'RedirectController@edit', 'as' => 'redirect.edit']);
$router->post('redirect/{id}/save', ['uses' => 'RedirectController@save', 'as' => 'redirect.save']);

$router->get('import/catalogue', ['uses' => 'ImportCatalogueController@products', 'as' => 'import.products']);
$router->post('import/catalogue/upload', ['uses' => 'ImportCatalogueController@upload', 'as' => 'import.products.upload']);
$router->get('import/catalogue/cat', ['uses' => 'ImportCatalogueController@categories', 'as' => 'import.categories']);
$router->post('import/catalogue/cat/upload', ['uses' => 'ImportCatalogueController@uploadCategories', 'as' => 'import.categories.upload']);
$router->get('import/customer', ['uses' => 'ImportCustomerController@customers', 'as' => 'import.customers']);
$router->post('import/customer/upload', ['uses' => 'ImportCustomerController@upload', 'as' => 'import.customers.upload']);

$router->get('category', ['uses' => 'CategoryController@list', 'as' => 'category.list']);
$router->post('category/move', ['uses' => 'CategoryController@move', 'as' => 'category.move']);
$router->post('category/{id}/delete', ['uses' => 'CategoryController@deletePost', 'as' => 'category.delete.post']);
$router->get('category/add', ['uses' => 'CategoryController@add', 'as' => 'category.add']);
$router->get('category/{id}/delete', ['uses' => 'CategoryController@delete', 'as' => 'category.delete']);
$router->get('category/{id}/edit', ['uses' => 'CategoryController@edit', 'as' => 'category.edit']);
$router->post('category/{id}/save', ['uses' => 'CategoryController@save', 'as' => 'category.save']);

$router->post('category-file/{id}/delete', ['uses' => 'CategoryFileController@delete', 'as' => 'category-file.delete']);
$router->post('category-file/{id}/upload', ['uses' => 'CategoryFileController@upload', 'as' => 'category-file.upload']);
$router->post('category-file/{id}/sort', ['uses' => 'CategoryFileController@sort', 'as' => 'category-file.sort']);

$router->get('attribute', ['uses' => 'AttributeController@list', 'as' => 'attribute.list']);
$router->get('attribute/add', ['uses' => 'AttributeController@add', 'as' => 'attribute.add']);
$router->get('attribute/{id}/delete', ['uses' => 'AttributeController@delete', 'as' => 'attribute.delete']);
$router->get('attribute/{id}/edit', ['uses' => 'AttributeController@edit', 'as' => 'attribute.edit']);
$router->post('attribute/{id}/save', ['uses' => 'AttributeController@save', 'as' => 'attribute.save']);

$router->post('product-file/{id}/delete', ['uses' => 'ProductFileController@delete', 'as' => 'product.file.delete']);
$router->post('product-file/{id}/upload', ['uses' => 'ProductFileController@upload', 'as' => 'product.file.upload']);
$router->post('product-file/{id}/sort', ['uses' => 'ProductFileController@sort', 'as' => 'product.file.sort']);

$router->get('customer', ['uses' => 'CustomerController@list', 'as' => 'customer.list']);
$router->get('customer/add', ['uses' => 'CustomerController@add', 'as' => 'customer.add']);
$router->get('customer/{id}/edit', ['uses' => 'CustomerController@edit', 'as' => 'customer.edit']);
$router->post('customer/{id}/save', ['uses' => 'CustomerController@save', 'as' => 'customer.save']);
$router->get('customer/{id}/delete', ['uses' => 'CustomerController@delete', 'as' => 'customer.delete']);

$router->get('customer/group', ['uses' => 'CustomerController@groupList', 'as' => 'customer.group.list']);
$router->get('customer/group/add', ['uses' => 'CustomerController@groupAdd', 'as' => 'customer.group.add']);
$router->get('customer/group/{id}/edit', ['uses' => 'CustomerController@groupEdit', 'as' => 'customer.group.edit']);
$router->post('customer/group/{id}/save', ['uses' => 'CustomerController@groupSave', 'as' => 'customer.group.save']);
$router->get('customer/group/{id}/delete', ['uses' => 'CustomerController@groupDelete', 'as' => 'customer.group.delete']);

$router->get('customer/countries', ['uses' => 'CustomerController@countriesEdit', 'as' => 'customer.countries']);
$router->post('customer/countries', ['uses' => 'CustomerController@countriesSave', 'as' => 'customer.countries.save']);

$router->get('customer/carts', ['uses' => 'AbandonedCartController@list', 'as' => 'customer.abandoned-cart.list']);
$router->get('customer/carts/{id}', ['uses' => 'AbandonedCartController@view', 'as' => 'customer.abandoned-cart.view']);

$router->get('promotion', ['uses' => 'PromotionController@list', 'as' => 'promotion.list']);
$router->get('promotion/add', ['uses' => 'PromotionController@add', 'as' => 'promotion.add']);
$router->get('promotion/{id}/edit', ['uses' => 'PromotionController@edit', 'as' => 'promotion.edit']);
$router->post('promotion/{id}/save', ['uses' => 'PromotionController@save', 'as' => 'promotion.save']);
$router->get('promotion/{id}/delete', ['uses' => 'PromotionController@delete', 'as' => 'promotion.delete']);

$router->get('system/search/reindex', ['uses' => 'SearchController@reindex', 'as' => 'system.search.reindex']);

$router->get('system/vat', ['uses' => 'VatController@overview', 'as' => 'system.vat']);
$router->post('system/vat/settings', ['uses' => 'VatController@settingsSave', 'as' => 'system.vat.settings.save']);
$router->get('system/vat/class/add', ['uses' => 'VatController@classEdit', 'as' => 'system.vat.class.add']);
$router->get('system/vat/class/{id}/edit', ['uses' => 'VatController@classEdit', 'as' => 'system.vat.class.edit']);
$router->post('system/vat/class/{id}/save', ['uses' => 'VatController@classSave', 'as' => 'system.vat.class.save']);
$router->get('system/vat/class/{id}/delete', ['uses' => 'VatController@classDelete', 'as' => 'system.vat.class.delete']);
$router->get('system/vat/zone/add', ['uses' => 'VatController@zoneEdit', 'as' => 'system.vat.zone.add']);
$router->get('system/vat/zone/{id}/edit', ['uses' => 'VatController@zoneEdit', 'as' => 'system.vat.zone.edit']);
$router->post('system/vat/zone/{id}/save', ['uses' => 'VatController@zoneSave', 'as' => 'system.vat.zone.save']);
$router->get('system/vat/zone/{id}/delete', ['uses' => 'VatController@zoneDelete', 'as' => 'system.vat.zone.delete']);
$router->get('system/vat/rule/add', ['uses' => 'VatController@ruleEdit', 'as' => 'system.vat.rule.add']);
$router->get('system/vat/rule/{id}/edit', ['uses' => 'VatController@ruleEdit', 'as' => 'system.vat.rule.edit']);
$router->post('system/vat/rule/{id}/save', ['uses' => 'VatController@ruleSave', 'as' => 'system.vat.rule.save']);
$router->get('system/vat/rule/{id}/delete', ['uses' => 'VatController@ruleDelete', 'as' => 'system.vat.rule.delete']);

$router->get('system/shipping', ['uses' => 'ShippingController@manage', 'as' => 'system.shipping']);
$router->post('system/shipping/save', ['uses' => 'ShippingController@save', 'as' => 'system.shipping.save']);
$router->get('system/shipping/table-rates/{method}', ['uses' => 'ShippingController@tableRates', 'as' => 'system.shipping.table-rates']);
$router->get('system/payment', ['uses' => 'PaymentController@manage', 'as' => 'system.payment']);
$router->post('system/payment/save', ['uses' => 'PaymentController@save', 'as' => 'system.payment.save']);

$router->get('system/email', ['uses' => 'EmailController@list', 'as' => 'system.email']);
$router->post('system/email', ['uses' => 'EmailController@updateDefaults', 'as' => 'system.email.defaults']);
$router->get('system/email/{id}/edit', ['uses' => 'EmailController@edit', 'as' => 'system.email.edit']);
$router->post('system/email/{id}/save', ['uses' => 'EmailController@save', 'as' => 'system.email.save']);
$router->get('system/email/{id}/preview', ['uses' => 'EmailController@preview', 'as' => 'system.email.preview']);
$router->get('system/email/{id}/preview-frame', ['uses' => 'EmailController@previewFrame', 'as' => 'system.email.preview.frame']);
$router->post('system/email/{id}/preview', ['uses' => 'EmailController@previewTest', 'as' => 'system.email.preview.test']);

$router->get('system/store', ['uses' => 'StoreController@list', 'as' => 'system.store']);
$router->post('system/store', ['uses' => 'StoreController@save', 'as' => 'system.store.update']);
$router->get('system/pdf', ['uses' => 'PDFController@editSettings', 'as' => 'system.pdf']);
$router->post('system/pdf', ['uses' => 'PDFController@updateSettings', 'as' => 'system.pdf.update']);

$router->get('', ['uses' => 'DashboardController@dashboard', 'as' => 'dashboard']);
$router->get('{other}', ['uses' => 'DashboardController@undefinedRoute', 'as' => '404'])->where('other', '.*');
<?php

namespace CoasterCommerce\Core\Providers;

use CoasterCommerce\Core\CatalogueUrls\CatalogueUrls;
use CoasterCommerce\Core\CatalogueUrls\UrlResolver;
use CoasterCommerce\Core\Contracts\Cart as CartContract;
use CoasterCommerce\Core\Menu\AdminMenu;
use CoasterCommerce\Core\Menu\FrontendMenu;
use CoasterCommerce\Core\MessageAlerts\FrontendAlert;
use CoasterCommerce\Core\Middleware\Admin;
use CoasterCommerce\Core\Middleware\Api;
use CoasterCommerce\Core\Middleware\SessionSaving;
use CoasterCommerce\Core\Middleware\CatalogueRoute;
use CoasterCommerce\Core\Middleware\Customer;
use CoasterCommerce\Core\Middleware\Guest;
use CoasterCommerce\Core\Middleware\MessageAlerts;
use CoasterCommerce\Core\Model\Customer as CustomerModel;
use CoasterCommerce\Core\Model\Order\ItemPrice;
use CoasterCommerce\Core\Model\Order\ItemTaxClass;
use CoasterCommerce\Core\Model\Product\Attribute;
use CoasterCommerce\Core\Model\Product\AttributeCache;
use CoasterCommerce\Core\Session\Cart;
use CoasterCommerce\Core\Console;
use CoasterCommerce\Core\Session\WishList;
use Illuminate\Auth\AuthManager;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Routing\Router;
use Illuminate\Config\Repository;
use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Illuminate\Support\Str;
use Illuminate\View\Factory;
use Exception;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * @var array
     */
    protected $listen = [
        'CoasterCms\Events\Admin\LoadResponse' => [
            'CoasterCommerce\Core\Listeners\AdminCoasterResponse',
        ],
        'CoasterCommerce\Core\Events\AdminProductSave' => [
            'CoasterCommerce\Core\Listeners\AdminProductSave',
        ],
        'CoasterCommerce\Core\Events\AdminProductMassUpdate' => [
            'CoasterCommerce\Core\Listeners\AdminProductMassUpdate',
        ],
        'CoasterCommerce\Core\Events\AdminCategorySave' => [
            'CoasterCommerce\Core\Listeners\AdminCategorySave',
        ],
        'CoasterCommerce\Core\Events\AdminAttributeSave' => [
            'CoasterCommerce\Core\Listeners\AdminAttributeSave',
        ],
        'CoasterCommerce\Core\Events\AdminCustomerSave' => [
            'CoasterCommerce\Core\Listeners\AdminCustomerSave',
        ],
        'CoasterCommerce\Core\Events\AdminEmailSave' => [
            'CoasterCommerce\Core\Listeners\AdminEmailSave',
        ],
        'CoasterCommerce\Core\Events\AdminPromotionSave' => [
            'CoasterCommerce\Core\Listeners\AdminPromotionSave',
        ],
        'CoasterCommerce\Core\Events\FrontendInit' => [
            'CoasterCommerce\Core\Listeners\FrontendInit',
        ],
        'CoasterCommerce\Core\Events\AdminProductGroupAttributes' => [
            'CoasterCommerce\Core\Listeners\AdminProductGroupAttributes',
        ],
        'CoasterCommerce\Core\Events\CategoryRenderContentFields' => [
            'CoasterCommerce\Core\Listeners\AddCategoryContentFields',
        ],
        'CoasterCommerce\Core\Events\OrderPlaced' => [
            'CoasterCommerce\Core\Listeners\OrderPlaced',
        ],
        'CoasterCommerce\Core\Events\OrderPaymentMethods' => [
            'CoasterCommerce\Core\Listeners\OrderClickCollect',
        ],
        'CoasterCommerce\Core\Events\ValidateOrderAddress' => [
            'CoasterCommerce\Core\Listeners\ValidateOrderAddressRules',
        ],
        'CoasterCms\Events\Admin\ThemeBuilderInit' => [
            'CoasterCommerce\Core\Listeners\ThemeBuilderInit',
        ],
    ];

    /**
    * @var array
    */
    protected $_commands = [
        Console\EmailInStock::class,
        Console\EmailAbandonedCart::class,
        Console\IndexPrice::class,
        Console\IndexSearch::class,
        Console\OGLStockLevels::class,
        Console\OGLExportOrder::class
    ];

    /**
     * Bootstrap any application services.
     *
     * @param Repository $config
     * @param Factory $view
     * @param Router $router
     * @param Dispatcher $events
     * @return void
     * @throws Exception
     */
    public function boot(Repository $config, Factory $view, Router $router, Dispatcher $events)
    {
        // register migrations
        $this->app->make('migrator')->path(coaster_commerce_base_path('database/migrations'));

        // register view
        $this->loadViewsFrom(coaster_commerce_base_path('resources/views'), 'coaster-commerce');

        // register default config
        $this->mergeConfigFrom(coaster_commerce_base_path('config/coaster-commerce.php'), 'coaster-commerce');

        // register translation files
        $this->loadTranslationsFrom(coaster_commerce_base_path('resources/lang'), 'coaster-commerce');

        // publishable config / view files
        $this->publishes([
            coaster_commerce_base_path('config/coaster-commerce.php') => config_path('coaster-commerce.php'),
        ], 'coaster-commerce.config');
        $this->publishes([
            coaster_commerce_base_path('resources/assets') => public_path(config('coaster-commerce.url.assets'))
        ], 'coaster-commerce.assets');
        $this->publishes([
            coaster_commerce_base_path('resources/views') => resource_path('views/vendor/coaster-commerce')
        ], 'coaster-commerce.views');

        // coaster commerce middleware
        $router->aliasMiddleware('coaster-commerce.admin', Admin::class);
        $router->aliasMiddleware('coaster-commerce.api', Api::class);
        $router->aliasMiddleware('coaster-commerce.customer', Customer::class);
        $router->aliasMiddleware('coaster-commerce.guest', Guest::class);

        $router->pushMiddlewareToGroup('coaster.cms', CatalogueRoute::class);
        $router->pushMiddlewareToGroup('web', MessageAlerts::class);
        $router->pushMiddlewareToGroup('web', SessionSaving::class);

        // coaster commerce frontend message alerts
        $this->app->alias('coaster-commerce.message-alerts.frontend', FrontendAlert::class);
        $this->app->singleton('coaster-commerce.message-alerts.frontend', function ($app) {
            return new FrontendAlert($app['session.store'], $app['view']);
        });

        // coaster commerce catalog urls helper and resolver
        $this->app->alias('coaster-commerce.catalog-urls', CatalogueUrls::class);
        $this->app->singleton('coaster-commerce.catalog-urls', function () {
            return new CatalogueUrls();
        });
        $this->app->alias('coaster-commerce.url-resolver', UrlResolver::class);
        $this->app->singleton('coaster-commerce.url-resolver', function () {
            return new UrlResolver();
        });

        // coaster commerce admin menu (load default items)
        $this->app->alias('coaster-commerce.admin-menu', AdminMenu::class);
        $this->app->singleton('coaster-commerce.admin-menu', function () {
            return (new AdminMenu())->setDefaults();
        });

        // Customer account menu
        $this->app->singleton('coaster-commerce.customer-menu', function (Application $app) {
            return (new FrontendMenu(['cart' => $app['coaster-commerce.cart']]))->loadCustomerMenu();
        });

        // Wish list session data provider
        $this->app->alias('coaster-commerce.wishlist', WishList::class);
        $this->app->singleton('coaster-commerce.wishlist', function (Application $app) {
            return new WishList(
                $app['session.store'],
                $app['coaster-commerce.cart']
            );
        });

        // Cart session data provider
        $this->app->alias('coaster-commerce.cart', Cart::class);
        $this->app->bind(CartContract::class, 'coaster-commerce.cart');
        $this->app->singleton('coaster-commerce.cart', function (Application $app) {
            return new Cart(
                $app['session.store'],
                $app['auth'],
                $app['url'],
                $app['view']
            );
        });

        // set as global view data
        $view->share($config->get('coaster-commerce.cart.var'), $this->app['coaster-commerce.cart']); // default is $cart
        $view->share('formBuilder', $this->app['form']);

        // str helper
        Str::macro('cutString', function ($string, $length) {
            if (strlen($string) < $length) {
                return $string;
            } else {
                $str = static::random(15);
                $string = substr($string, 0, strpos(wordwrap($string, $length, "/$str/"), "/$str/"));
                return substr($string, -1) == '.' ? $string : $string . '...';
            }
        });

        // load eav/model/input classes for product attributes
        $this->loadProductAttributeData();

        // product price calculation modifiers
        $this->app->alias('coaster-commerce.order.item-price', ItemPrice::class);
        $this->app->singleton('coaster-commerce.order.item-price', function (Application $app) {
            return new ItemPrice();
        });
        $this->app->alias('coaster-commerce.order.item-tax-class', ItemTaxClass::class);
        $this->app->singleton('coaster-commerce.order.item-tax-class', function (Application $app) {
            return new ItemTaxClass();
        });

        // listeners to make menu / dash changes in coaster cms
        foreach ($this->listen as $event => $listeners) {
            foreach ($listeners as $listener) {
                $events->listen($event, $listener);
            }
        }
    }

    /**
     *
     */
    public function loadProductAttributeData()
    {
        // load eav types
        $this->app->alias('coaster-commerce.product.eav', Attribute\EavTypes::class);
        $this->app->singleton('coaster-commerce.product.eav', function (Application $app) {
            return new Attribute\EavTypes([
                'datetime' => Attribute\Eav\DatetimeAttribute::class,
                'decimal' => Attribute\Eav\DecimalAttribute::class,
                'float' => Attribute\Eav\FloatAttribute::class,
                'integer' => Attribute\Eav\IntegerAttribute::class,
                'string' => Attribute\Eav\StringAttribute::class,
                'text' => Attribute\Eav\TextAttribute::class,
            ]);
        });

        // load model types
        $this->app->alias('coaster-commerce.product.model', Attribute\ModelTypes::class);
        $this->app->singleton('coaster-commerce.product.model', function (Application $app) {
            return new Attribute\ModelTypes([
                'datetime' => new Attribute\Model\DatetimeModel(),
                'json' => new Attribute\Model\JsonModel(),
                'stock' => new Attribute\Model\StockModel(),
                'file' => new Attribute\Model\FileModel(),
                'category' => new Attribute\Model\CategoryModel()
            ]);
        });

        // load frontend types
        $this->app->alias('coaster-commerce.product.frontend', Attribute\FrontendTypes::class);
        $this->app->singleton('coaster-commerce.product.frontend', function (Application $app) {
            return new Attribute\FrontendTypes([
                'text' => new Attribute\Frontend\TextFrontend(),
                'number' => new Attribute\Frontend\NumberFrontend(),
                'textarea' => new Attribute\Frontend\TextareaFrontend(),
                'wysiwyg' => new Attribute\Frontend\WysiwygFrontend(),
                'select' => new Attribute\Frontend\SelectFrontend(),
                'select-multiple' => new Attribute\Frontend\SelectMultipleFrontend(),
                'date' => new Attribute\Frontend\DateFrontend(),
                'switch' => new Attribute\Frontend\SwitchFrontend(),
                'price' => new Attribute\Frontend\PriceFrontend(),
                'gallery' => new Attribute\Frontend\GalleryFrontend(),
                'variation_attributes' => new Attribute\Frontend\ProductVariationsFrontend(),
                'stock' => new Attribute\Frontend\StockFrontend(),
                'sku' => new Attribute\Frontend\SkuFrontend(),
            ]);
        });

        // populate attribute cache
        AttributeCache::$eavTypes = app('coaster-commerce.product.eav');
        AttributeCache::$modelTypes = app('coaster-commerce.product.model');
        AttributeCache::$frontendTypes = app('coaster-commerce.product.frontend');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        // make sure cms provider is registered first
        $this->app->register('CoasterCms\CmsServiceProvider');
        
        $this->commands($this->_commands);

        /** @var Repository $config */
        $config = $this->app['config'];
        /** @var AuthManager $authManager */
        $authManager = $this->app['auth'];

        // set driver / guard / password reset conf
        $config->set('auth.guards.cc-customer', ['driver' => 'session', 'provider' => 'cc-customer']);
        $config->set('auth.providers.cc-customer', ['driver' => 'cc-customer-driver', 'model' => CustomerModel::class]);
        $config->set('auth.passwords.cc-customer', ['provider' => 'cc-customer', 'table' => 'cc_customer_resets', 'expire' => 60]);
        // register custom driver for auth provider
        $authManager->provider('cc-customer-driver', function ($app, $config) {
            return new CustomerModel\AuthDriver($app['hash'], $config['model']);
        });
    }
}

<p align="center"><img src="https://www.coastercms.org/uploads/images/logo_coaster_github4.jpg"></p>

<p align="center">
  <a href="https://packagist.org/packages/coastercms/coastercommerce"><img src="https://poser.pugx.org/coastercms/coastercommerce/downloads.svg"></a>
  <a href="https://packagist.org/packages/coastercms/coastercommerce"><img src="https://poser.pugx.org/coastercms/coastercommerce/version.svg"></a>
  <a href="https://www.gnu.org/licenses/gpl-3.0.en.html"><img src="https://poser.pugx.org/coastercms/coastercommerce/license.svg"></a>
</p>

This is a ecommerce addon designed to work on top of the Coaster CMS framework (https://github.com/CoasterCms/coastercms).

## Install Addon

The steps are are as follows:

1. Install the framework is you haven't already https://github.com/CoasterCms/framework
2. Go to the root directory of your project
3. Run <code>composer require coastercms/coastercommerce:~8.0</code> to install ecomm package
4. Run <code>php artisan migrate</code> to create ecomm database tables
5. Run <code>php artisan vendor:publish --tag=coaster-commerce.assets</code> to publish ecomm admin assets
6. Add the provider CoasterCommerce\Core\Providers\RoutesProvider::class to your config/app.php file (before CoasterRoutesProvider)
7. Login to admin and you should see an "Ecomm" link in the top right menu

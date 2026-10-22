# Upgrading from Coaster Commerce 8 to 13

Coaster Commerce 13 requires Coaster CMS 13 (`coastercms/framework:^13.0`), Laravel 13 and PHP 8.3+.
Follow the Coaster CMS upgrade notes first:
https://github.com/CoasterCms/framework/blob/13.x/UPGRADE-13.md

13.0 is a compatibility release with no new features.

## Changes that can affect a site

- `league/iso3166` is now `^4.0` (2.x only installs on PHP 7). `alpha2()` / `alpha3()` return the
  same arrays.
- Mailable callbacks receive `Symfony\Component\Mime\Email`. Custom mailables extending
  `AbstractMailable` that add callbacks need updating.
- `Customer`, `Order`, `Promotion` and `Product\SearchIndex\Price` declare `datetime` casts instead
  of `$dates`. A subclass that declares its own `$casts` must include these.
- Explicitly nullable parameters (`?Product $product = null` etc.) on `Order`, `Order\Item`,
  `Order\Payment`, `Order\Shipping`, `Order\PDF`, `Promotion::getActivePromotions`,
  `Customer\WishList\Item` and `Renderer\Admin\CategoryList`. Overrides must use the same signatures.
- Published views: `request()->get()` becomes `request()->input()`, and `Collective\Html\FormBuilder`
  docblocks become `LaravelLux\Html\FormBuilder`.

## Known

- `paypal/paypal-checkout-sdk` is abandoned upstream. The PayPal payment method still installs and
  loads, but has not been re-tested on 13.

<?php

namespace CoasterCommerce\Core\Model;

use Carbon\Carbon;
use CoasterCommerce\Core\Model\Customer\Group;
use CoasterCommerce\Core\Model\Promotion\CategoriesPivot;
use CoasterCommerce\Core\Model\Promotion\Coupon;
use CoasterCommerce\Core\Model\Promotion\CustomerGroupsPivot;
use CoasterCommerce\Core\Model\Promotion\CustomersPivot;
use CoasterCommerce\Core\Model\Promotion\ProductsPivot;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Promotion
 * @package CoasterCommerce\Core\Model
 * @property Coupon[]|Collection $coupons
 */
class Promotion extends Model
{

    public $table = 'cc_promotions';

    public $dates = ['active_from', 'active_to'];

    protected $_allCategories;

    protected static $_activePromotions;

    protected static $_indexFlag = false;

    /**
     * @return HasMany
     */
    public function coupons()
    {
        return $this->hasMany(Coupon::class);
    }

    /**
     * @return BelongsToMany
     */
    public function customers()
    {
        return $this->belongsToMany(Customer::class, 'cc_promotion_customers');
    }

    /**
     * @return BelongsToMany
     */
    public function customerGroups()
    {
        return $this->belongsToMany(Group::class, 'cc_promotion_groups');
    }

    /**
     * @return HasMany
     */
    public function customersPivot()
    {
        return $this->hasMany(CustomersPivot::class);
    }

    /**
     * @return HasMany
     */
    public function customerGroupsPivot()
    {
        return $this->hasMany(CustomerGroupsPivot::class);
    }

    /**
     * @return BelongsToMany
     */
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'cc_promotion_categories');
    }

    /**
     * @return BelongsToMany
     */
    public function products()
    {
        return $this->belongsToMany(Product::class, 'cc_promotion_products');
    }

    /**
     * @return HasMany
     */
    public function categoriesPivot()
    {
        return $this->hasMany(CategoriesPivot::class);
    }

    /**
     * @return HasMany
     */
    public function productsPivot()
    {
        return $this->hasMany(ProductsPivot::class);
    }

    /**
     * Loads possible anchor cats as well as directly selected cats
     * @return array
     */
    public function allCategories()
    {
        if (is_null($this->_allCategories)) {
            $this->_allCategories = [];
            $anchorPaths = [];
            foreach ($this->categories as $category) {
                $this->_allCategories[] = $category->id;
                if ($category->anchor) {
                    $anchorPaths[] = $category->fullPath();
                }
            }
            if ($anchorPaths) {
                $catQuery = (new Category())->newQuery()->whereIn('path', $anchorPaths);
                foreach ($anchorPaths as $anchorPath) {
                    $catQuery->orWhere('path', 'LIKE', $anchorPath . '/%');
                }
                $anchorCats = $catQuery->select('id')->pluck('id')->toArray();
                $this->_allCategories = array_unique(array_merge($this->_allCategories, $anchorCats));
            }
        }
        return $this->_allCategories;
    }

    /**
     * @param float $price
     * @return float
     */
    public function applyDiscount($price)
    {
        if ($this->discount_type == 'fixed') {
            $price -= $this->discount_amount;
        } else {
            $price *= (100 - $this->discount_amount) / 100;
        }
        return max($price, 0);
    }

    /**
     * @return string
     */
    public function activeDateText()
    {
        $dateFormat = 'Y-m-d H:i:s';
        if ($this->active_from && $this->active_to) {
            return $this->active_from->format($dateFormat) . ' - ' . $this->active_to->format($dateFormat);
        } elseif ($this->active_from) {
            return 'From ' . $this->active_from->format($dateFormat);
        } elseif ($this->active_to) {
            return 'Until ' . $this->active_to->format($dateFormat);
        } else {
            return 'Always';
        }
    }

    /**
     * if true will make getActivePromotions ignore customer checks and last flag
     * used in search price indexing
     * @param bool $value
     */
    public static function setIndexFlag($value)
    {
        static::$_indexFlag = $value;
    }

    /**
     * @param Product $product // if non product, will get promotions that apply to shipping
     * @param Customer|null $customer
     * @param string|array $couponCodes
     * @param array $ignoreIds
     * @return Collection|static[]
     */
    public static function getActivePromotions(Product $product = null, Customer $customer = null, $couponCodes = [], $ignoreIds = [])
    {
        $activePromotions = static::_getActivePromotions();
        if ($ignoreIds) {
            $activePromotions = $activePromotions->filter(function ($activePromotion) use($ignoreIds) {
                return !in_array($activePromotion->id, $ignoreIds);
            });
        }

        // filter customers
        if (!static::$_indexFlag) {
            $activePromotions = $activePromotions->filter(function ($activePromotion) use ($customer) {
                $groupIds = $activePromotion->customerGroupsPivot->pluck('group_id')->toArray();
                $customerIds = $activePromotion->customersPivot->pluck('customer_id')->toArray();
                return (!$customerIds && !$groupIds) || ($customer && (in_array($customer->group_id, $groupIds) || in_array($customer->id, $customerIds)));
            });
        }

        if ($product) {
            // filter item rules, then specific products (& categories)
            $activePromotions = $activePromotions->filter(function ($activePromotion) {
                return $activePromotion->type == 'item';
            });
            $activePromotions = $activePromotions->filter(function (Promotion $activePromotion) use ($product) {
                $productIds = $activePromotion->productsPivot->pluck('product_id')->toArray();
                $filter = in_array($product->id, $productIds);
                if (!$filter && !$activePromotion->include_products) {
                    // filter based on category if product exclude set on promotion and current product is not listed
                    $productCategories = $product->categories->pluck('id')->toArray();
                    $catFilter = array_intersect($productCategories, $activePromotion->allCategories());
                    $filter = $activePromotion->include_categories ? !$catFilter : $catFilter;
                } else {
                    // for all other cases just filter on based product as it should override category rules anyway
                    $filter = $activePromotion->include_products ? !$filter : $filter;
                }
                return !$filter;
            });
        } else {
            // filter cart rules
            $activePromotions = $activePromotions->filter(function (Promotion $activePromotion) {
                return $activePromotion->type == 'cart';
            });
        }

        // coupons
        $couponCodes = array_map('strtolower', is_array($couponCodes) ? $couponCodes : [$couponCodes]);
        $activePromotions = $activePromotions->filter(function (Promotion $activePromotion) use ($couponCodes) {
            foreach ($activePromotion->coupons as $coupon) {
                if (in_array(strtolower($coupon->code), $couponCodes) && $coupon->hasUses()) {
                    return true;
                }
            }
            return $activePromotion->coupons->isEmpty();
        });

        // check for is last flag on promotions and remove subsequent promotions if found
        if (!static::$_indexFlag) {
            $slice = 0;
            foreach ($activePromotions as $activePromotion) {
                $slice++;
                if ($activePromotion->is_last) {
                    break;
                }
            }
            return $activePromotions->slice(0, $slice);
        } else {
            return $activePromotions;
        }
    }

    /**
     * @param string|array $couponCodes
     */
    public function reduceCouponUses($couponCodes)
    {
        $couponCodes = array_map('strtolower', is_array($couponCodes) ? $couponCodes : [$couponCodes]);
        foreach ($this->coupons as $coupon) {
            if (in_array(strtolower($coupon->code), $couponCodes) && $coupon->uses_left > 0) {
                $coupon->uses_left -= 1;
                $coupon->save();
                break; // only use one coupon per promotion
            }
        }
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        if ($this->enabled == 0) {
            return false;
        } elseif ($this->active_from || $this->active_to) {
            return (new Carbon())->between(
                $this->active_from ?: Carbon::parse('-10 seconds'),
                $this->active_to ?:  Carbon::parse('+10 seconds')
            );
        }
        return true;
    }

    /**
     * @return array
     */
    public function affectedProductIds()
    {
        $productIds = $this->productsPivot->pluck('product_id')->toArray();
        if (!$this->include_products) {
            $catProductIds = CategoryProducts::whereIn('category_id', $this->allCategories())->pluck('product_id')->toArray();
            if ($this->include_categories) {
                $productIds = array_diff($catProductIds, $productIds);
            } else {
                $productIds = array_merge($productIds, $catProductIds);
                $productIds = Product::whereNotIn('id', $productIds)->pluck('id')->toArray();
            }
        }
        return array_unique($productIds);
    }

    /**
     * @return static[]|Collection
     */
    protected static function _getActivePromotions()
    {
        if (is_null(static::$_activePromotions)) {
            static::$_activePromotions = static::with(['customersPivot', 'customerGroupsPivot', 'categories', 'productsPivot'])
                ->where('enabled', 1)
                ->where(function (Builder $q) {
                    $q->whereNull('active_from')->orWhere('active_from', '<=', (new Carbon())->toDateTimeString());
                })->where(function (Builder $q) {
                    $q->whereNull('active_to')->orWhere('active_to', '>', (new Carbon())->toDateTimeString());
                })->orderBy('priority', 'asc')->get();
        }
        return static::$_activePromotions;
    }

}

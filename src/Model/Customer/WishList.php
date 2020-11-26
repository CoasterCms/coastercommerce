<?php

namespace CoasterCommerce\Core\Model\Customer;

use Carbon\Carbon;
use CoasterCommerce\Core\Model\Customer\WishList\Item;
use CoasterCommerce\Core\Model\Product;
use CoasterCommerce\Core\Model\Product\Variation;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Class WishList
 * @package CoasterCommerce\Core\Model\Customer
 * @property WishList\Item[]|Collection $items
 */
class WishList extends Model
{

    /**
     * @var string
     */
    protected $table = 'cc_customer_wishlists';

    /**
     * @var array
     */
    protected $with = ['items'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function items()
    {
        return $this->hasMany(WishList\Item::class, 'wishlist_id')->with(['product', 'variation']);
    }

    /**
     * @param int $productId
     * @return Item
     * @throws \Exception
     */
    public function addProduct($productId)
    {
        if (!($product = Product::find($productId))) {
            throw new \Exception(__('coaster-commerce::frontend.wishlist_invalid_item'));
        }
        $item = new Item();
        $item->loadProduct($product);
        return $this->addItem($item);
    }

    /**
     * @param int $variationId
     * @return Item
     * @throws \Exception
     */
    public function addProductVariation($variationId)
    {
        if (!($variation = Variation::find($variationId))) {
            throw new \Exception(__('coaster-commerce::frontend.wishlist_item_add_fail'));
        }
        if (!($product = Product::find($variation->product_id))) {
            throw new \Exception(__('coaster-commerce::frontend.wishlist_item_add_fail'));
        }
        $item = new Item();
        $item->loadProduct($product);
        $item->loadVariation($variation);
        return $this->addItem($item);
    }

    /**
     * @param WishList\Item $item
     * @return WishList\Item
     */
    public function addItem($item)
    {
        $this->saveIfNotExists();
        $item->setRelation('wishList', $item);
        $uniqFields = array_fill_keys(['product_id', 'variation_id'],null);
        $itemUniqInfo = array_intersect_key($item->attributesToArray(), $uniqFields) + $uniqFields;
        foreach ($this->items as $otherItem) {
            if ($itemUniqInfo === array_intersect_key($otherItem->attributesToArray(), $uniqFields)) {
                return $otherItem;
            }
        }
        $this->items()->save($item);
        return $item;
    }

    /**
     * Save if list does not exist in database
     */
    public function saveIfNotExists()
    {
        if (!$this->id) {
            $this->save();
        }
    }

    /**
     * Give list a name if it doesn't have one
     * @param array $options
     * @return bool|void
     */
    public function save(array $options = [])
    {
        if (!$this->name) {
            $this->name = 'Saved Items - ' . (new Carbon())->format('jS F Y');
        }
        if (!$this->share_key) {
            $this->share_key = Str::random(16);
        }
        $this->customer_ip = request()->ip();
        parent::save($options);
    }

    /**
     * Gets active list for customer
     * @param int $customerId
     * @return static|null
     */
    public static function loadCustomerList($customerId)
    {
        return (new static)->newQuery()->where('customer_id', $customerId)->orderBy('selected', 'DESC')->first();
    }

}

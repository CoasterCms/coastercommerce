<?php

namespace CoasterCommerce\Core\Model\Customer\WishList;

use CoasterCommerce\Core\Model\Customer\WishList;
use CoasterCommerce\Core\Model\Product;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{

    /**
     * @var string
     */
    protected $table = 'cc_customer_wishlist_items';

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function wishList()
    {
        return $this->belongsTo(WishList::class, 'wishlist_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function variation()
    {
        return $this->belongsTo(Product\Variation::class, 'variation_id');
    }

    /**
     * @param Product $product
     */
    public function loadProduct(Product $product = null)
    {
        if ($product) {
            $this->setRelation('product', $product);
            $this->product_id = $product->id;
        }
    }

    /**
     * @param Product\Variation $variation
     */
    public function loadVariation(Product\Variation $variation = null)
    {
        if ($variation) {
            $this->setRelation('variation', $variation);
            $this->variation_id = $variation->id;
        }
    }

}

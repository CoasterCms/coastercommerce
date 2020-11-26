<?php

namespace CoasterCommerce\Core\Model\Product;

use CoasterCommerce\Core\Model\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Related extends Model
{

    public $table = 'cc_product_related';

    /**
     * Return enabled
     * @param array|int $ids
     * @param array|string|null $types
     * @param bool $includeDisabled
     * @return Product[]|Collection
     */
    public function getRelatedProductsFromIds($ids, $types = null, $includeDisabled = false)
    {
        return $this->getRelatedProductQueryFromIds($ids, $types, $includeDisabled)->get();
    }

    /**
     * Return enabled
     * @param array|int $ids
     * @param array|string|null $types
     * @param bool $includeDisabled
     * @return Builder
     */
    public function getRelatedProductQueryFromIds($ids, $types = null, $includeDisabled = false)
    {
        $ids = array_filter(is_array($ids) ? $ids : [$ids]);
        $types = array_filter(is_array($types) ? $types : [$types]);

        $relatedQ = static::newQuery()->whereIn('product_id', $ids);
        foreach ($types as $type) {
            $relatedQ->where($type, 1);
        }
        $relatedProductIds = $relatedQ->pluck('related_product_id')->unique()->toArray();
        $relatedProductIds = array_diff($relatedProductIds, $ids); // make sure all related ids do not match any passed ids

        $relatedProductQ = Product::with(['variations', 'categories'])->whereIn('id', $relatedProductIds);
        if (!$includeDisabled) {
            $relatedProductQ->where('enabled', 1);
        }
        return $relatedProductQ;
    }

}

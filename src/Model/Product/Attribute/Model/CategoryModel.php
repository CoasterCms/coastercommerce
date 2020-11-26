<?php

namespace CoasterCommerce\Core\Model\Product\Attribute\Model;

use CoasterCommerce\Core\Model\Product;
use CoasterCommerce\Core\Model\Product\Attribute;
use Illuminate\Database\Eloquent\Builder;

class CategoryModel extends AbstractModel
{

    /**
     * @param array $productAttributes
     * @param Product $product
     * @return mixed
     */
    public function processVirtual($productAttributes, $product)
    {
        return $product->relationLoaded('categories') ? $product->categories->pluck('id')->toArray() : [];
    }

    /**
     * @param Attribute $attribute
     * @param mixed $filterValue
     * @param Builder $query
     * @return Builder
     */
    public function filterQuery($attribute, $filterValue, $query)
    {
        return $query->whereHas('categories', function ($query) use($filterValue) {
            $query->whereIn('category_id', $filterValue);
        });
    }

}

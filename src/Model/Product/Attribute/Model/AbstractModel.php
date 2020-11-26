<?php

namespace CoasterCommerce\Core\Model\Product\Attribute\Model;

use CoasterCommerce\Core\Model\Product;
use CoasterCommerce\Core\Model\Product\Attribute;
use Illuminate\Database\Eloquent\Builder;

abstract class AbstractModel
{

    /**
     * Processes the value when loading product model/collection
     * @param mixed $value
     * @return mixed
     */
    public function databaseToCollection($value)
    {
        return $value;
    }

    /**
     * Processes the value when before saving to database
     * @param mixed $value
     * @return mixed
     */
    public function collectionToDatabase($value)
    {
        return $value;
    }

    /**
     * Generates a virtual attribute value
     * @param array $productAttributes
     * @param Product $product
     * @return mixed
     */
    public function processVirtual($productAttributes, $product)
    {
        return null;
    }

    /**
     * Columns needed to generate virtual data
     * @return array
     */
    public function columnsForVirtual()
    {
        return [];
    }

    /**
     * @param Attribute $attribute
     * @param mixed $filterValue
     * @param Builder $query
     * @return Builder
     */
    public function filterQuery($attribute, $filterValue, $query)
    {
        return $query->where($attribute->code, $filterValue);
    }

}

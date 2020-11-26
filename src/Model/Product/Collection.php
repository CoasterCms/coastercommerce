<?php

namespace CoasterCommerce\Core\Model\Product;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use CoasterCommerce\Core\Model\Product;

class Collection extends EloquentCollection
{

    /**
     * Get the collection of items as a plain array.
     *
     * @return array
     */
    public function toAttributeArray()
    {
        return array_map(function ($value) {
            /** @var Product $value */
            return $value instanceof Arrayable ? $value->toAttributeArray() : $value;
        }, $this->items);
    }

}

<?php

namespace CoasterCommerce\Core\Model\Product\Attribute\Frontend;

use CoasterCommerce\Core\Model\Product\Attribute;
use Illuminate\Database\Eloquent\Builder;

class TextFrontend extends AbstractFrontend
{

    /**
     * @param Attribute $attribute
     * @param mixed $filterValue
     * @param Builder $query
     * @return Builder
     */
    public function filterQuery($attribute, $filterValue, $query)
    {
        return $query->where($attribute->code, 'LIKE', '%' . $filterValue . '%');
    }

}

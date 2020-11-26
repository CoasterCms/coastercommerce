<?php

namespace CoasterCommerce\Core\Model\Product\Attribute\Frontend;

use CoasterCommerce\Core\Model\Product\Attribute;
use CoasterCommerce\Core\Model\Product\Variation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class SkuFrontend extends AbstractFrontend
{

    protected $_inputView = 'text';

    protected $_filterView = 'text';

    /**
     * @param Attribute $attribute
     * @param mixed $filterValue
     * @param Builder $query
     * @return Builder
     */
    public function filterQuery($attribute, $filterValue, $query)
    {
        // get productIds where variations have matching sku
        $pIds = DB::table((new Variation())->getTable())
            ->where('sku', 'LIKE', '%' . $filterValue . '%')
            ->select('product_id')->groupBy('product_id')
            ->pluck('product_id')->toArray();
        // add orWhere with productIds in addition to normal check on main sku field
        return $query->where(function (Builder $q) use($attribute, $filterValue, $pIds) {
            $q->where($attribute->code, 'LIKE', '%' . $filterValue . '%')
                ->orWhereIn('id', $pIds);
        });
    }

}

<?php

namespace CoasterCommerce\Core\Model\Product\Attribute\Frontend;

use CoasterCommerce\Core\Model\Product\Attribute;
use Illuminate\Database\Eloquent\Builder;

class StockFrontend extends TextFrontend
{

    // needed for custom frontend filter view
    // StockModel is used for filterQuery as stock should be a virtual attribute

}

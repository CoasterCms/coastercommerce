<?php

namespace CoasterCommerce\Core\Model\Promotion;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{

    /**
     * @var string
     */
    protected $table = 'cc_promotion_coupons';

    /**
     * @return bool
     */
    public function hasUses()
    {
        return is_null($this->uses_left) ||  $this->uses_left > 0;
    }

}

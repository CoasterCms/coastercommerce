<?php

namespace CoasterCommerce\Core\Model\Order\Payment\PayPal;

use CoasterCommerce\Core\Model\Order;
use Illuminate\Database\Eloquent\Model as Eloquent;

class Model extends Eloquent
{

    /**
     * @var string
     */
    protected $table = 'cc_order_payment_paypal';

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    /**
     * @param string $ppId)
     * @return static
     */
    public static function findWithPPId($ppId)
    {
        return (new static)->where('pp_id', $ppId)->first();
    }

}

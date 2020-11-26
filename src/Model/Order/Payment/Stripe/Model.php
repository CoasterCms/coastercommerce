<?php

namespace CoasterCommerce\Core\Model\Order\Payment\Stripe;

use CoasterCommerce\Core\Model\Order;
use Illuminate\Database\Eloquent\Model as Eloquent;

class Model extends Eloquent
{

    /**
     * @var string
     */
    protected $table = 'cc_order_payment_stripe';

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    /**
     * @param string $sessionId
     * @return static
     */
    public static function findWithSession($sessionId)
    {
        return (new static)->where('session_id', $sessionId)->first();
    }

}

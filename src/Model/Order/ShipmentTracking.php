<?php

namespace CoasterCommerce\Core\Model\Order;

use Illuminate\Database\Eloquent\Model;

class ShipmentTracking extends Model
{

    /**
     * @var string
     */
    public $table = 'cc_order_shipment_tracking';

    /**
     * @var array
     */
    public $with = ['courier'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function courier()
    {
        return $this->belongsTo(ShippingCourier::class, 'courier_id');
    }

    /**
     * @return string
     */
    public function link()
    {
        return $this->courier ? str_replace('%number', $this->number, $this->courier->link) : '';
    }

}

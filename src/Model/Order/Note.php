<?php

namespace CoasterCommerce\Core\Model\Order;

use CoasterCommerce\Core\Model\Order;
use Illuminate\Database\Eloquent\Model;

class Note extends Model
{

    /**
     * @var string
     */
    protected $table = 'cc_order_notes';

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

}

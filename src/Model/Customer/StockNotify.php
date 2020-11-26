<?php

namespace CoasterCommerce\Core\Model\Customer;

use Illuminate\Database\Eloquent\Model;

class StockNotify extends Model
{

    public $table = 'cc_customer_stock_notify';

    public $fillable = ['email', 'product_id'];

}

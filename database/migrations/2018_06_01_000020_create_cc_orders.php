<?php

use CoasterCommerce\Core\Database\Blueprint;
use CoasterCommerce\Core\Database\Migration;
use CoasterCommerce\Core\Model\Order;

class CreateCcOrders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->_schema->create('cc_orders', function(Blueprint $table) {
            $table->increments('id');
            $table->integer('customer_id')->unsigned()->nullable();
            $table->foreign('customer_id')->references('id')->on('cc_customers')->onDelete('set null');
            $table->string('email')->nullable();
            $table->string('order_coupon')->nullable();
            $table->string('order_number')->unique()->nullable();
            $table->string('order_status');
            $table->string('shipping_method')->nullable();
            $table->timestamp('shipment_sent')->nullable();
            $table->string('payment_method')->nullable();
            $table->timestamp('payment_confirmed')->nullable();
            $table->decimal('order_subtotal_ex_vat', 12, 4);
            $table->decimal('order_subtotal_vat', 12, 4);
            $table->decimal('order_subtotal_inc_vat', 12, 4);
            $table->decimal('order_subtotal_discount_ex_vat',  12, 4);
            $table->decimal('order_subtotal_discount_vat',  12, 4);
            $table->decimal('order_subtotal_discount_inc_vat',  12, 4);
            $table->decimal('order_shipping_ex_vat',  12, 4);
            $table->decimal('order_shipping_vat',  12, 4);
            $table->decimal('order_shipping_inc_vat',  12, 4);
            $table->decimal('order_shipping_discount_ex_vat',  12, 4);
            $table->decimal('order_shipping_discount_vat',  12, 4);
            $table->decimal('order_shipping_discount_inc_vat',  12, 4);
            $table->decimal('order_total_ex_vat',  12, 4);
            $table->decimal('order_total_vat',  12, 4);
            $table->decimal('order_total_inc_vat',  12, 4);
            $table->string('order_vat_type');
            $table->timestamp('order_placed')->nullable();
            $table->string('order_key')->nullable();
            $table->string('customer_ip')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->_schema->drop('cc_orders');
    }
}

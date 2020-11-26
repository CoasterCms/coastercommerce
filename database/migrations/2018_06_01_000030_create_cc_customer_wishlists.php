<?php

use CoasterCommerce\Core\Database\Blueprint;
use CoasterCommerce\Core\Database\Migration;

class CreateCcCustomerWishlists extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->_schema->create('cc_customer_wishlists', function(Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->boolean('selected')->default(0);
            $table->integer('customer_id')->unsigned()->nullable();
            $table->foreign('customer_id')->references('id')->on('cc_customers')->onDelete('cascade');
            $table->integer('guest_order_id')->unsigned()->nullable();
            $table->foreign('guest_order_id')->references('id')->on('cc_orders')->onDelete('cascade');
            $table->string('customer_ip')->nullable();
            $table->string('share_key');
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
        $this->_schema->drop('cc_customer_wishlists');
    }
}

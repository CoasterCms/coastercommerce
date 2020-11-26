<?php

use CoasterCommerce\Core\Database\Blueprint;
use CoasterCommerce\Core\Database\Migration;

class CreateCcOrderItems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->_schema->create('cc_order_items', function(Blueprint $table) {
            $table->increments('id');
            $table->integer('order_id')->unsigned();
            $table->foreign('order_id')->references('id')->on('cc_orders')->onDelete('cascade');
            $table->integer('product_id')->unsigned()->nullable();
            $table->foreign('product_id')->references('id')->on('cc_products')->onDelete('set null');
            $table->integer('variation_id')->unsigned()->nullable();
            $table->foreign('variation_id')->references('id')->on('cc_product_variations')->onDelete('set null');
            $table->string('item_name');
            $table->string('item_sku');
            $table->text('item_data')->nullable();
            $table->boolean('item_virtual')->default(0);
            $table->decimal('item_base_price_ex_vat', 10, 4);
            $table->decimal('item_base_price_inc_vat', 10, 4);
            $table->decimal('item_discount_ex_vat', 10, 4);
            $table->decimal('item_discount_inc_vat', 10, 4);
            $table->decimal('item_price_ex_vat', 10, 4);
            $table->decimal('item_unit_vat', 10, 4);
            $table->decimal('item_price_inc_vat', 10, 4);
            $table->integer('item_request_qty');
            $table->integer('item_qty');
            $table->decimal('item_total_ex_vat', 10, 4);
            $table->decimal('item_total_vat', 10, 4);
            $table->decimal('item_total_inc_vat', 10, 4);
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
        $this->_schema->drop('cc_order_items');
    }
}

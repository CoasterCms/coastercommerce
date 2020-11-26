<?php

use CoasterCommerce\Core\Database\Blueprint;
use CoasterCommerce\Core\Database\Migration;

class CreateCcProductRelated extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->_schema->create('cc_product_related', function(Blueprint $table) {
            $table->increments('id');
            $table->integer('product_id')->unsigned();
            $table->foreign('product_id')->references('id')->on('cc_products')->onDelete('cascade');
            $table->integer('related_product_id')->unsigned();
            $table->foreign('related_product_id')->references('id')->on('cc_products')->onDelete('cascade');
            $table->boolean('related')->default(0);
            $table->boolean('up_sell')->default(0);
            $table->boolean('cross_sell')->default(0);
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
        $this->_schema->drop('cc_product_related');
    }
}

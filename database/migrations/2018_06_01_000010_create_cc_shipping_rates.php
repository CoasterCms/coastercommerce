<?php

use CoasterCommerce\Core\Database\Blueprint;
use CoasterCommerce\Core\Database\Migration;

class CreateCcShippingRates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->_schema->create('cc_shipping_rates', function(Blueprint $table) {
            $table->increments('id');
            $table->string('method');
            $table->foreign('method')->references('code')->on('cc_shipping_methods')->onDelete('cascade');
            $table->string('country_iso3')->nullable();
            $table->string('postcode')->nullable();
            $table->string('condition_filter')->nullable();
            $table->float('condition_min')->nullable();
            $table->float('condition_max')->nullable();
            $table->decimal('shipping_rate');
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
        $this->_schema->drop('cc_shipping_rates');
    }
}

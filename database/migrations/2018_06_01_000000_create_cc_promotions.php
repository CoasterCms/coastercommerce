<?php

use CoasterCommerce\Core\Database\Blueprint;
use CoasterCommerce\Core\Database\Migration;

class CreateCcPromotions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->_schema->create('cc_promotions', function(Blueprint $table) {
            $table->increments('id');
            $table->boolean('enabled')->default(1);
            $table->string('name')->unique();
            $table->enum('type', ['item', 'cart']);
            $table->dateTime('active_from')->nullable();
            $table->dateTime('active_to')->nullable();
            $table->enum('discount_type', ['fixed', 'percent']);
            $table->decimal('discount_amount');
            $table->integer('priority')->default(1);
            $table->boolean('is_last')->default(0);
            $table->boolean('include_categories')->default(0);
            $table->boolean('include_products')->default(0);
            $table->boolean('apply_to_shipping')->default(0);
            $table->boolean('apply_to_subtotal')->default(1);
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
        $this->_schema->drop('cc_promotions');
    }
}

<?php

use CoasterCommerce\Core\Database\Blueprint;
use CoasterCommerce\Core\Database\Migration;

class CreateCcProductVariations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->_schema->create('cc_product_variations', function(Blueprint $table) {
            $table->increments('id');
            $table->integer('product_id')->unsigned();
            $table->foreign('product_id')->references('id')->on('cc_products')->onDelete('cascade');
            $table->text('variation');
            $table->boolean('enabled')->default(1);
            $table->string('sku')->nullable();
            $table->integer('stock_qty')->nullable();
            $table->boolean('fixed_price')->default(0);
            $table->decimal('price')->nullable();
            $table->decimal('weight')->nullable();
            $table->string('image')->nullable();
            $table->integer('sort_value');
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
        $this->_schema->drop('cc_product_variations');
    }
}


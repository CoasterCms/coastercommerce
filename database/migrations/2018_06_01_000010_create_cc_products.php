<?php

use CoasterCommerce\Core\Database\Blueprint;
use CoasterCommerce\Core\Database\Migration;

class CreateCcProducts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->_schema->create('cc_products', function(Blueprint $table) {
            $table->increments('id');
            $table->boolean('enabled');
            $table->string('name');
            $table->string('sku')->unique();
            $table->string('url_key')->unique();
            $table->integer('tax_class_id')->unsigned();
            $table->foreign('tax_class_id')->references('id')->on('cc_tax_classes');
            $table->decimal('price');
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
        $this->_schema->drop('cc_products');
    }
}

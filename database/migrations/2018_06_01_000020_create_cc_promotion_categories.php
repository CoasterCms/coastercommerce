<?php

use CoasterCommerce\Core\Database\Blueprint;
use CoasterCommerce\Core\Database\Migration;

class CreateCcPromotionCategories extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->_schema->create('cc_promotion_categories', function(Blueprint $table) {
            $table->increments('id');
            $table->integer('promotion_id')->unsigned();
            $table->foreign('promotion_id')->references('id')->on('cc_promotions')->onDelete('cascade');
            $table->integer('category_id')->unsigned();
            $table->foreign('category_id')->references('id')->on('cc_categories')->onDelete('cascade');
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
        $this->_schema->drop('cc_promotion_categories');
    }
}

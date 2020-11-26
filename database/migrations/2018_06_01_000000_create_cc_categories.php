<?php

use CoasterCommerce\Core\Database\Blueprint;
use CoasterCommerce\Core\Database\Migration;

class CreateCcCategories extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->_schema->create('cc_categories', function(Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('url_key');
            $table->boolean('enabled')->default(1);
            $table->boolean('anchor')->default(1);
            $table->boolean('featured')->default(0);
            $table->boolean('menu')->default(1);
            $table->string('path')->nullable();
            $table->integer('position')->default(0);
            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();
            $table->string('meta_keywords')->nullable();
            $table->text('images')->nullable();
            $table->text('intro')->nullable();
            $table->text('content')->nullable();
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
        $this->_schema->drop('cc_categories');
    }
}

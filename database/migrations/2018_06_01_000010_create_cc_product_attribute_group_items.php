<?php

use CoasterCommerce\Core\Database\Blueprint;
use CoasterCommerce\Core\Database\Migration;

class CreateCcProductAttributeGroupItems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->_schema->create('cc_product_attribute_group_items', function(Blueprint $table) {
            $table->increments('id');
            $table->integer('group_id')->unsigned();
            $table->foreign('group_id')->references('id')->on('cc_product_attribute_groups')->onDelete('cascade');
            $table->integer('attribute_id')->unsigned();
            $table->foreign('attribute_id')->references('id')->on('cc_product_attributes')->onDelete('cascade');
            $table->integer('position')->default(0);
            $table->timestamps();
        });

        $this->_db->table('cc_product_attribute_group_items')->insert([
            [
                'group_id' => 1,
                'attribute_id' => 4,
                'position' => 10,
            ],
            [
                'group_id' => 1,
                'attribute_id' => 5,
                'position' => 20,
            ],
            [
                'group_id' => 1,
                'attribute_id' => 6,
                'position' => 30,
            ],
            [
                'group_id' => 1,
                'attribute_id' => 7,
                'position' => 40,
            ],
            [
                'group_id' => 1,
                'attribute_id' => 8,
                'position' => 50,
            ],
            [
                'group_id' => 7,
                'attribute_id' => 9,
                'position' => 10,
            ],
            [
                'group_id' => 2,
                'attribute_id' => 10,
                'position' => 20,
            ],
            [
                'group_id' => 2,
                'attribute_id' => 11,
                'position' => 30,
            ],
            [
                'group_id' => 2,
                'attribute_id' => 12,
                'position' => 10,
            ],
            [
                'group_id' => 7,
                'attribute_id' => 13,
                'position' => 20,
            ],
            [
                'group_id' => 7,
                'attribute_id' => 14,
                'position' => 30,
            ],
            [
                'group_id' => 7,
                'attribute_id' => 15,
                'position' => 40,
            ],
            [
                'group_id' => 4,
                'attribute_id' => 16,
                'position' => 20,
            ],
            [
                'group_id' => 4,
                'attribute_id' => 17,
                'position' => 10,
            ],
            [
                'group_id' => 1,
                'attribute_id' => 19,
                'position' => 70,
            ],
            [
                'group_id' => 1,
                'attribute_id' => 20,
                'position' => 60,
            ],
            [
                'group_id' => 5,
                'attribute_id' => 21,
                'position' => 10,
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->_schema->drop('cc_product_attribute_group_items');
    }
}

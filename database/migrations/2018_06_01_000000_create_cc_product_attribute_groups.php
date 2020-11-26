<?php

use CoasterCommerce\Core\Database\Blueprint;
use CoasterCommerce\Core\Database\Migration;

class CreateCcProductAttributeGroups extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->_schema->create('cc_product_attribute_groups', function(Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->integer('position');
            $table->timestamps();
        });

        $this->_db->table('cc_product_attribute_groups')->insert([
            [
                'name' => 'General',
                'position' => 10
            ],
            [
                'name' => 'Content',
                'position' => 20
            ],
            [
                'name' => 'Advanced Pricing',
                'position' => 30
            ],
            [
                'name' => 'Stock',
                'position' => 40
            ],
            [
                'name' => 'Variations',
                'position' => 40
            ],
            [
                'name' => 'Related Products',
                'position' => 60
            ],
            [
                'name' => 'Meta Information',
                'position' => 100
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
        $this->_schema->drop('cc_product_attribute_groups');
    }
}

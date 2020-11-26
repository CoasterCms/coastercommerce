<?php

use CoasterCommerce\Core\Database\Blueprint;
use CoasterCommerce\Core\Database\Migration;

class CreateCcProductEav extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->_schema->create('cc_product_eav', function(Blueprint $table) {
            $table->increments('id');
            $table->integer('attribute_id')->unsigned();
            $table->foreign('attribute_id')->references('id')->on('cc_product_attributes')->onDelete('cascade');
            $table->boolean('is_system')->default(0);
            $table->string('datatype');
            $table->timestamps();
        });

        $this->_db->table('cc_product_eav')->insert([
            [
                'attribute_id' => 10,
                'datatype' => 'text',
                'is_system' => 0
            ],
            [
                'attribute_id' => 11,
                'datatype' => 'text',
                'is_system' => 0
            ],
            [
                'attribute_id' => 12,
                'datatype' => 'text',
                'is_system' => 0
            ],
            [
                'attribute_id' => 13,
                'datatype' => 'string',
                'is_system' => 0
            ],
            [
                'attribute_id' => 14,
                'datatype' => 'string',
                'is_system' => 0
            ],
            [
                'attribute_id' => 15,
                'datatype' => 'string',
                'is_system' => 0
            ],
            [
                'attribute_id' => 16,
                'datatype' => 'integer',
                'is_system' => 1
            ],
            [
                'attribute_id' => 17,
                'datatype' => 'integer',
                'is_system' => 1
            ],
            [
                'attribute_id' => 19,
                'datatype' => 'integer',
                'is_system' => 1
            ],
            [
                'attribute_id' => 21,
                'datatype' => 'text',
                'is_system' => 1
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
        $this->_schema->drop('cc_product_eav');
    }
}

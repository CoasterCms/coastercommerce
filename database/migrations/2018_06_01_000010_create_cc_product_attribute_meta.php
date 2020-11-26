<?php

use CoasterCommerce\Core\Database\Blueprint;
use CoasterCommerce\Core\Database\Migration;

class CreateCcProductAttributeMeta extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->_schema->create('cc_product_attribute_meta', function(Blueprint $table) {
            $table->increments('id');
            $table->integer('attribute_id')->unsigned();
            $table->foreign('attribute_id')->references('id')->on('cc_product_attributes')->onDelete('cascade');
            $table->string('key');
            $table->text('value');
            $table->timestamps();
        });

        $this->_db->table('cc_product_attribute_meta')->insert([
            [
                'attribute_id' => 8,
                'key' => 'source',
                'value' => 'CoasterCommerce\Core\Model\Product\Attribute\OptionSource\TaxClass'
            ],
            [
                'attribute_id' => 20,
                'key' => 'source',
                'value' => 'CoasterCommerce\Core\Model\Product\Attribute\OptionSource\Category'
            ],
            [
                'attribute_id' => 4,
                'key' => 'default',
                'value' => '1'
            ],
            [
                'attribute_id' => 13,
                'key' => 'length-guide',
                'value' => '40,60'
            ],
            [
                'attribute_id' => 14,
                'key' => 'length-guide',
                'value' => '40,60'
            ],
            [
                'attribute_id' => 15,
                'key' => 'length-guide',
                'value' => '120,156'
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
        $this->_schema->drop('cc_product_attribute_meta');
    }
}

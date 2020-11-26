<?php

use CoasterCommerce\Core\Database\Blueprint;
use CoasterCommerce\Core\Database\Migration;

class CreateCcShippingMethods extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->_schema->create('cc_shipping_methods', function(Blueprint $table) {
            $table->string('code')->primary();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('active')->default(1);
            $table->integer('sort_value');
            $table->string('class');
            $table->decimal('min_cart_total')->nullable();
            $table->decimal('max_cart_total')->nullable();
            $table->text('custom_config')->nullable();
            $table->timestamps();
        });

        $this->_db->table('cc_shipping_methods')->insert([
            [
                'code' => 'free_shipping',
                'name' => 'Free Shipping',
                'class' => \CoasterCommerce\Core\Model\Order\Shipping\FreeShipping::class,
                'sort_value' => 10,
                'active' => 0,
                'custom_config' => null
            ],
            [
                'code' => 'flat_rate',
                'name' => 'Standard Shipping',
                'class' => \CoasterCommerce\Core\Model\Order\Shipping\FlatRate::class,
                'sort_value' => 20,
                'active' => 1,
                'custom_config' => '{"fixed_rate":10}'
            ],
            [
                'code' => 'table_rate',
                'name' => 'Standard Shipping',
                'class' => \CoasterCommerce\Core\Model\Order\Shipping\TableRate::class,
                'sort_value' => 30,
                'active' => 0,
                'custom_config' => '{"cart_field":"weight"}'
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->_schema->drop('cc_shipping_methods');
    }
}

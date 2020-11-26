<?php

use CoasterCommerce\Core\Database\Blueprint;
use CoasterCommerce\Core\Database\Migration;
use CoasterCommerce\Core\Model\Order;

class CreateCcOrderStatus extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->_schema->create('cc_order_status', function(Blueprint $table) {
            $table->string('code')->primary();
            $table->enum('state', Order::stateArray());
            $table->boolean('state_default')->default(0);
            $table->string('name');
            $table->string('colour')->nullable();
            $table->boolean('visible')->default(1);
            $table->timestamps();
        });

        $this->_db->table('cc_order_status')->insert([
            'code' => 'quote',
            'state' => 'quote',
            'name' => 'Quote',
            'state_default' => 1,
            'colour' => '',
            'visible' => 0
        ]);

        $this->_db->table('cc_order_status')->insert([
            'code' => 'payment_gateway',
            'state' => 'quote',
            'name' => 'At Payment Gateway',
            'state_default' => 0,
            'colour' => '',
            'visible' => 0
        ]);

        $this->_db->table('cc_order_status')->insert([
            'code' => 'pending',
            'state' => 'processing',
            'name' => 'Pending Payment',
            'state_default' => 0,
            'colour' => '',
            'visible' => 1
        ]);

        $this->_db->table('cc_order_status')->insert([
            'code' => 'processing',
            'state' => 'processing',
            'name' => 'Processing',
            'state_default' => 1,
            'colour' => '',
            'visible' => 1
        ]);

        $this->_db->table('cc_order_status')->insert([
            'code' => 'on_hold',
            'state' => 'processing',
            'name' => 'On Hold',
            'state_default' => 0,
            'colour' => '',
            'visible' => 1
        ]);

        $this->_db->table('cc_order_status')->insert([
            'code' => 'complete',
            'state' => 'complete',
            'name' => 'Complete',
            'state_default' => 1,
            'colour' => '',
            'visible' => 1
        ]);

        $this->_db->table('cc_order_status')->insert([
            'code' => 'cancelled',
            'state' => 'cancelled',
            'name' => 'Cancelled',
            'state_default' => 1,
            'colour' => '',
            'visible' => 1
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->_schema->drop('cc_order_status');
    }

}

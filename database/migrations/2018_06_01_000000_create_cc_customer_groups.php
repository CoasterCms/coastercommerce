<?php

use CoasterCommerce\Core\Database\Blueprint;
use CoasterCommerce\Core\Database\Migration;

class CreateCcCustomerGroups extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->_schema->create('cc_customer_groups', function(Blueprint $table) {
            $table->increments('id');
            $table->string('name')->unique();
            $table->decimal('discount')->nullable();
            $table->timestamps();
        });

        $this->_db->table('cc_customer_groups')->insert([
            'name' => 'Logged In'
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->_schema->drop('cc_customer_groups');
    }
}

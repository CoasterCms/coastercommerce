<?php

use CoasterCommerce\Core\Database\Blueprint;
use CoasterCommerce\Core\Database\Migration;

class CreateCcCustomers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->_schema->create('cc_customers', function(Blueprint $table) {
            $table->increments('id');
            $table->string('email')->unique();
            $table->integer('group_id')->unsigned();
            $table->foreign('group_id')->references('id')->on('cc_customer_groups');
            $table->string('remember_token', 100)->nullable();
            $table->string('password');
            $table->timestamp('last_login')->nullable();
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
        $this->_schema->drop('cc_customers');
    }
}

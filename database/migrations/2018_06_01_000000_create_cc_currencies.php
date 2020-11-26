<?php

use CoasterCommerce\Core\Database\Blueprint;
use CoasterCommerce\Core\Database\Migration;

class CreateCcCurrencies extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->_schema->create('cc_currencies', function(Blueprint $table) {
            $table->increments('id');
            $table->string('name')->unique();
            $table->string('prefix')->nullable();
            $table->string('suffix')->nullable();
            $table->timestamps();
        });

        $this->_db->table('cc_currencies')->insert([
            'name' => 'GBP',
            'prefix' => '&pound;',
            'suffix' => null
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->_schema->drop('cc_currencies');
    }
}

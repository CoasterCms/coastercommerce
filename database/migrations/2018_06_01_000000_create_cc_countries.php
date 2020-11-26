<?php

use CoasterCommerce\Core\Database\Blueprint;
use CoasterCommerce\Core\Database\Migration;

class CreateCcCountries extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->_schema->create('cc_countries', function(Blueprint $table) {
            $table->increments('id');
            $table->string('iso3')->unique();
            $table->string('name')->nullable();
            $table->timestamps();
        });

        $this->_db->table('cc_countries')->insert([
            'iso3' => 'GBR',
            'name' => 'United Kingdom'
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->_schema->drop('cc_countries');
    }
}

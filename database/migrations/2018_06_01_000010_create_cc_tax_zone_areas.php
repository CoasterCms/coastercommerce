<?php

use CoasterCommerce\Core\Database\Blueprint;
use CoasterCommerce\Core\Database\Migration;

class CreateCcTaxZoneAreas extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->_schema->create('cc_tax_zone_areas', function(Blueprint $table) {
            $table->increments('id');
            $table->integer('tax_zone_id')->unsigned();
            $table->foreign('tax_zone_id')->references('id')->on('cc_tax_zones')->onDelete('cascade');
            $table->string('country_iso3');
            $table->timestamps();
        });

        $this->_db->table('cc_tax_zone_areas')->insert([
            'tax_zone_id' => 1,
            'country_iso3' => 'GBR',
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->_schema->drop('cc_tax_zone_areas');
    }
}

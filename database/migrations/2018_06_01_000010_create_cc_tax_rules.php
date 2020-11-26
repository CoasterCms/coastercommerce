<?php

use CoasterCommerce\Core\Database\Blueprint;
use CoasterCommerce\Core\Database\Migration;

class CreateCcTaxRules extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->_schema->create('cc_tax_rules', function(Blueprint $table) {
            $table->increments('id');
            $table->string('name')->unique();
            $table->integer('tax_class_id')->unsigned();
            $table->foreign('tax_class_id')->references('id')->on('cc_tax_classes')->onDelete('cascade');
            $table->integer('tax_zone_id')->unsigned();
            $table->foreign('tax_zone_id')->references('id')->on('cc_tax_zones')->onDelete('cascade');
            $table->integer('customer_group_id')->unsigned()->nullable();
            $table->foreign('customer_group_id')->references('id')->on('cc_customer_groups')->onDelete('cascade');
            $table->integer('priority');
            $table->float('percentage');
            $table->timestamps();
        });

        $this->_db->table('cc_tax_rules')->insert([
            'name' => 'Default',
            'tax_class_id' => 1,
            'tax_zone_id' => 1,
            'priority' => 1,
            'percentage' => 20
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->_schema->drop('cc_tax_rules');
    }
}

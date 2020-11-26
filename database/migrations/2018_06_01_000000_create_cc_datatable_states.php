<?php

use CoasterCommerce\Core\Database\Blueprint;
use CoasterCommerce\Core\Database\Migration;

class CreateCcDatatableStates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->_schema->create('cc_datatable_states', function(Blueprint $table) {
            $table->increments('id');
            $table->string('email');
            $table->string('name');
            $table->text('table_state')->nullable();
            $table->text('filter_state')->nullable();
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
        $this->_schema->drop('cc_datatable_states');
    }
}

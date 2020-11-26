<?php

use CoasterCommerce\Core\Database\Blueprint;
use CoasterCommerce\Core\Database\Migration;

class CreateCcPDF extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->_schema->create('cc_pdf', function(Blueprint $table) {
            $table->increments('id');
            $table->string('setting')->unique();
            $table->string('label');
            $table->string('type')->default('text');
            $table->text('value')->nullable();
            $table->timestamps();
        });

        $this->_db->table('cc_pdf')->insert([
            [
                'setting' => 'pdf_logo',
                'label' => 'PDF Logo',
                'type' => 'file-standard',
                'value' => null
            ],
            [
                'setting' => 'pdf_header',
                'label' => 'PDF Header Text',
                'type' => 'textarea',
                'value' => null
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
        $this->_schema->drop('cc_pdf');
    }
}

<?php

use CoasterCommerce\Core\Database\Blueprint;
use CoasterCommerce\Core\Database\Migration;

class CreateCcPermissionRoles extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->_schema->create('cc_permission_roles', function(Blueprint $table) {
            $table->increments('id');
            $table->string('label');
            $table->integer('role_id')->nullable();
            $table->integer('user_id')->nullable();
            $table->timestamps();
        });

        $this->_db->table('cc_permission_roles')->insert([
            [
                'role_id' => 2,
                'label' => 'Shop Admin',
            ],
            [
                'role_id' => 3,
                'label' => 'General Shop Management',
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
        $this->_schema->drop('cc_permission_roles');
    }
}

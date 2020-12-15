<?php

use CoasterCommerce\Core\Database\Blueprint;
use CoasterCommerce\Core\Database\Migration;

class CreateCcPermissions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->_schema->create('cc_permissions', function(Blueprint $table) {
            $table->increments('id');
            $table->integer('role_id')->unsigned();
            $table->foreign('role_id')->references('id')->on('cc_permission_roles');
            $table->integer('action_id')->unsigned();
            $table->foreign('action_id')->references('id')->on('cc_permission_actions');
            $table->timestamps();
        });

        $this->_db->table('cc_permissions')->insert([
            [
                'role_id' => 1,
                'action_id' => 1,
            ],
            [
                'role_id' => 1,
                'action_id' => 2,
            ],
            [
                'role_id' => 1,
                'action_id' => 3,
            ],
            [
                'role_id' => 1,
                'action_id' => 4,
            ],
            [
                'role_id' => 1,
                'action_id' => 5,
            ],
            [
                'role_id' => 1,
                'action_id' => 6,
            ],
            [
                'role_id' => 1,
                'action_id' => 7,
            ],
            [
                'role_id' => 1,
                'action_id' => 8,
            ],
            [
                'role_id' => 1,
                'action_id' => 9,
            ],
            [
                'role_id' => 1,
                'action_id' => 10,
            ],
            [
                'role_id' => 1,
                'action_id' => 11,
            ],
            [
                'role_id' => 1,
                'action_id' => 12,
            ],
            [
                'role_id' => 1,
                'action_id' => 13,
            ],
            [
                'role_id' => 1,
                'action_id' => 14,
            ],
            [
                'role_id' => 1,
                'action_id' => 15,
            ],
            [
                'role_id' => 1,
                'action_id' => 16,
            ],
            [
                'role_id' => 1,
                'action_id' => 17,
            ],
            [
                'role_id' => 1,
                'action_id' => 18,
            ],
            [
                'role_id' => 1,
                'action_id' => 19,
            ],
            [
                'role_id' => 2,
                'action_id' => 1,
            ],
            [
                'role_id' => 2,
                'action_id' => 4,
            ],
            [
                'role_id' => 2,
                'action_id' => 5,
            ],
            [
                'role_id' => 2,
                'action_id' => 6,
            ],
            [
                'role_id' => 2,
                'action_id' => 8,
            ],
            [
                'role_id' => 2,
                'action_id' => 9,
            ],
            [
                'role_id' => 2,
                'action_id' => 10,
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
        $this->_schema->drop('cc_permissions');
    }
}

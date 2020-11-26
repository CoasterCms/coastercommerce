<?php

use CoasterCommerce\Core\Database\Blueprint;
use CoasterCommerce\Core\Database\Migration;

class CreateCcEmailSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->_schema->create('cc_email_settings', function(Blueprint $table) {
            $table->increments('id');
            $table->boolean('enabled')->default(1);
            $table->string('mailable')->unique();
            $table->string('label');
            $table->string('subject');
            $table->string('from_email')->nullable();
            $table->string('from_name')->nullable();
            $table->string('to')->nullable();
            $table->string('cc')->nullable();
            $table->string('bcc')->nullable();
            $table->timestamps();
        });

        $this->_db->table('cc_email_settings')->insert([
            [
                'mailable' => 'CoasterCommerce\Core\Mailables\ResetPasswordMailable',
                'label' => 'Password Reset (Forgotten Password)',
                'subject' => 'New Password'
            ],
            [
                'mailable' => 'CoasterCommerce\Core\Mailables\OrderMailable',
                'label' => 'Order Confirmation',
                'subject' => 'Order Confirmation %order_number'
            ],
            [
                'mailable' => 'CoasterCommerce\Core\Mailables\OrderNoteMailable',
                'label' => 'Order Note',
                'subject' => 'Order Updated %order_number'
            ],
            [
                'mailable' => 'CoasterCommerce\Core\Mailables\OrderShipmentMailable',
                'label' => 'Order Shipped',
                'subject' => 'Order Shipped %order_number'
            ],
            [
                'mailable' => 'CoasterCommerce\Core\Mailables\NewAccountMailable',
                'label' => 'New Account',
                'subject' => 'Account Registered'
            ],
            [
                'mailable' => 'CoasterCommerce\Core\Mailables\WishListMailable',
                'label' => 'Share Wish List',
                'subject' => 'My Wish List'
            ],
            [
                'mailable' => 'CoasterCommerce\Core\Mailables\AbandonedCartMailable',
                'label' => 'Abandoned Cart',
                'subject' => 'Items in your shopping cart'
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
        $this->_schema->drop('cc_email_settings');
    }
}

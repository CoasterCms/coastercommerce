<?php

use CoasterCommerce\Core\Database\Blueprint;
use CoasterCommerce\Core\Database\Migration;

class CreateCcSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->_schema->create('cc_settings', function(Blueprint $table) {
            $table->increments('id');
            $table->string('setting')->unique();
            $table->string('value');
            $table->timestamps();
        });

        $this->_db->table('cc_settings')->insert([
            [
                'setting' => 'store_name',
                'value' => 'Ecomm Store'
            ],
            [
                'setting' => 'store_email',
                'value' => ''
            ],
            [
                'setting' => 'store_phone',
                'value' => ''
            ],
            [
                'setting' => 'email_sender_name',
                'value' => 'Ecomm Store Support'
            ],
            [
                'setting' => 'email_sender_address',
                'value' => 'support@ecommwebsite.com'
            ],
            [
                'setting' => 'next_order_number',
                'value' => '1000001'
            ],
            [
                'setting' => 'customer_default_group',
                'value' => '1'
            ],
            [
                'setting' => 'country_default',
                'value' => 'GBR'
            ],
            [
                'setting' => 'country_rule',
                'value' => 'specific'
            ],
            [
                'setting' => 'vat_catalogue_price',
                'value' => 'ex'
            ],
            [
                'setting' => 'vat_catalogue_discount_calculation',
                'value' => 'ex'
            ],
            [
                'setting' => 'vat_catalogue_display',
                'value' => 'ex'
            ],
            [
                'setting' => 'vat_shipping_price',
                'value' => 'inc'
            ],
            [
                'setting' => 'vat_cart_discount_calculation',
                'value' => 'inc'
            ],
            [
                'setting' => 'vat_calculate_on',
                'value' => 'item'
            ],
            [
                'setting' => 'vat_cart_summary_display',
                'value' => ''
            ],
            [
                'setting' => 'vat_tax_class',
                'value' => '1'
            ],
            [
                'setting' => 'vat_tax_zone',
                'value' => '1'
            ],
            [
                'setting' => 'catalogue_pagination',
                'value' => 30,
            ],
            [
                'setting' => 'cc_key',
                'value' => ''
            ],
            [
                'setting' => 'recaptcha_secret_key',
                'value' => ''
            ],
            [
                'setting' => 'recaptcha_public_key',
                'value' => ''
            ],
            [
                'setting' => 'catalogue_redirect',
                'value' => '0'
            ],
            [
                'setting' => 'default_currency_id',
                'value' => '1'
            ],
            [
                'setting' => 'import_emails_enabled',
                'value' => '0'
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
        $this->_schema->drop('cc_settings');
    }
}

<?php

use CoasterCommerce\Core\Database\Blueprint;
use CoasterCommerce\Core\Database\Migration;

class CreateCcPaymentMethods extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->_schema->create('cc_payment_methods', function(Blueprint $table) {
            $table->string('code')->primary();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('active')->default(1);
            $table->integer('sort_value');
            $table->string('class');
            $table->decimal('min_cart_total')->nullable();
            $table->decimal('max_cart_total')->nullable();
            $table->string('order_status')->nullable();
            $table->foreign('order_status')->references('code')->on('cc_order_status')->onDelete('set null');
            $table->text('custom_config')->nullable();
            $table->timestamps();
        });

        $this->_db->table('cc_payment_methods')->insert([
            [
                'code' => 'cash',
                'name' => 'Pay by Cash/Cheque',
                'class' => \CoasterCommerce\Core\Model\Order\Payment\Cash::class,
                'sort_value' => 10,
                'active' => 1,
                'order_status' => 'pending'
            ],
            [
                'code' => 'stripe',
                'name' => 'Credit/Debit Card (Stripe)',
                'class' => \CoasterCommerce\Core\Model\Order\Payment\Stripe::class,
                'sort_value' => 20,
                'active' => 1,
                'order_status' => null
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
        $this->_schema->drop('cc_payment_methods');
    }
}

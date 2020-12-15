<?php

use CoasterCommerce\Core\Database\Blueprint;
use CoasterCommerce\Core\Database\Migration;

class CreateCcPermissionActions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->_schema->create('cc_permission_actions', function(Blueprint $table) {
            $table->increments('id');
            $table->string('action');
            $table->string('label');
            $table->integer('display_group')->default(1);
            $table->timestamps();
        });

        $this->_db->table('cc_permission_actions')->insert([
            [
                'action' => 'coaster-commerce.admin.order',
                'label' => 'Manage Orders',
                'display_group' => 1,
            ],
            [
                'action' => 'coaster-commerce.admin.system.payment',
                'label' => 'Configure Payment Methods',
                'display_group' => 1,
            ],
            [
                'action' => 'coaster-commerce.admin.system.shipping',
                'label' => 'Configure Shipping Methods',
                'display_group' => 1,
            ],
            [
                'action' => 'coaster-commerce.admin.promotion',
                'label' => 'Configure Discount/Promotions',
                'display_group' => 1,
            ],
            [
                'action' => 'coaster-commerce.admin.product',
                'label' => 'Manage Products',
                'display_group' => 2,
            ],
            [
                'action' => 'coaster-commerce.admin.product.mass-action',
                'label' => 'Mass Product Updates',
                'display_group' => 2,
            ],
            [
                'action' => 'coaster-commerce.admin.attribute',
                'label' => 'Configure Product Attributes',
                'display_group' => 2,
            ],
            [
                'action' => 'coaster-commerce.admin.category',
                'label' => 'Manage Categories',
                'display_group' => 2,
            ],
            [
                'action' => 'coaster-commerce.admin.redirect',
                'label' => 'Manage Catalog Redirects',
                'display_group' => 2,
            ],
            [
                'action' => 'coaster-commerce.admin.customer',
                'label' => 'Manage Customers',
                'display_group' => 3,
            ],
            [
                'action' => 'coaster-commerce.admin.customer.group',
                'label' => 'Manage Customer Groups',
                'display_group' => 3,
            ],
            [
                'action' => 'coaster-commerce.admin.customer.countries',
                'label' => 'Configure Allow Customer Countries',
                'display_group' => 3,
            ],
            [
                'action' => 'coaster-commerce.admin.system.email',
                'label' => 'Configure Email Recipients/Templates',
                'display_group' => 4,
            ],
            [
                'action' => 'coaster-commerce.admin.system.store',
                'label' => 'Configure Store Details (Used in Emails)',
                'display_group' => 4,
            ],
            [
                'action' => 'coaster-commerce.admin.system.vat',
                'label' => 'Configure VAT Settings',
                'display_group' => 4,
            ],
            [
                'action' => 'coaster-commerce.admin.import.products',
                'label' => 'Run Product Imports',
                'display_group' => 5,
            ],
            [
                'action' => 'coaster-commerce.admin.import.categories',
                'label' => 'Run Category Imports',
                'display_group' => 5,
            ],
            [
                'action' => 'coaster-commerce.admin.import.customers',
                'label' => 'Run Customer Imports',
                'display_group' => 5,
            ],
            [
                'action' => 'coaster-commerce.admin.permission',
                'label' => 'Manage Permissions (Full Admin)',
                'display_group' => 6,
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
        $this->_schema->drop('cc_permission_actions');
    }
}

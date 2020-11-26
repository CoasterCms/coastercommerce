<?php

use CoasterCommerce\Core\Database\Blueprint;
use CoasterCommerce\Core\Database\Migration;

class CreateCcProductAttributes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->_schema->create('cc_product_attributes', function(Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('code')->unique();
            $table->enum('type', ['default', 'eav', 'virtual']);
            $table->string('frontend');
            $table->string('model')->nullable();
            $table->integer('admin_filter')->default(0);
            $table->integer('admin_column')->default(0);
            $table->string('admin_validation')->nullable();
            $table->boolean('admin_massupdate')->default(0);
            $table->integer('search_weight')->default(0);
            $table->boolean('search_filter')->default(0);
            $table->timestamps();
        });

        $this->_db->table('cc_product_attributes')->insert([
            [
                'name' => 'Id',
                'code' => 'id',
                'type' => 'default',
                'frontend' => 'text',
                'model' => null,
                'admin_filter' => 10,
                'admin_column' => 10,
                'admin_validation' => null,
                'admin_massupdate' => 0,
            ],
            [
                'name' => 'Created At',
                'code' => 'created_at',
                'type' => 'default',
                'frontend' => 'date',
                'model' => 'datetime',
                'admin_filter' => 0,
                'admin_column' => 0,
                'admin_validation' => null,
                'admin_massupdate' => 0,
            ],
            [
                'name' => 'Updated At',
                'code' => 'updated_at',
                'type' => 'default',
                'frontend' => 'date',
                'model' => 'datetime',
                'admin_filter' => 0,
                'admin_column' => 0,
                'admin_validation' => null,
                'admin_massupdate' => 0,
            ],
            [
                'name' => 'Enabled',
                'code' => 'enabled',
                'type' => 'default',
                'frontend' => 'switch',
                'model' => null,
                'admin_filter' => 40,
                'admin_column' => 20,
                'admin_validation' => null,
                'admin_massupdate' => 1,
            ],
            [
                'name' => 'Name',
                'code' => 'name',
                'type' => 'default',
                'frontend' => 'text',
                'model' => null,
                'admin_filter' => 30,
                'admin_column' => 30,
                'admin_validation' => 'required',
                'admin_massupdate' => 1,
            ],
            [
                'name' => 'Sku',
                'code' => 'sku',
                'type' => 'default',
                'frontend' => 'sku',
                'model' => null,
                'admin_filter' => 20,
                'admin_column' => 40,
                'admin_validation' => 'required|unique:cc_products,sku[id]',
                'admin_massupdate' => 0,
            ],
            [
                'name' => 'Price',
                'code' => 'price',
                'type' => 'default',
                'frontend' => 'price',
                'model' => null,
                'admin_filter' => 70,
                'admin_column' => 50,
                'admin_validation' => 'required|max:1000000',
                'admin_massupdate' => 1,
            ],
            [
                'name' => 'Tax Class',
                'code' => 'tax_class_id',
                'type' => 'default',
                'frontend' => 'select',
                'model' => null,
                'admin_filter' => 0,
                'admin_column' => 0,
                'admin_validation' => null,
                'admin_massupdate' => 1,
            ],
            [
                'name' => 'Url',
                'code' => 'url_key',
                'type' => 'default',
                'frontend' => 'text',
                'model' => null,
                'admin_filter' => 0,
                'admin_column' => 0,
                'admin_validation' => 'required',
                'admin_massupdate' => 0,
            ],
            [
                'name' => 'Short Description',
                'code' => 'short_description',
                'type' => 'eav',
                'frontend' => 'wysiwyg',
                'model' => null,
                'admin_filter' => 0,
                'admin_column' => 0,
                'admin_validation' => null,
                'admin_massupdate' => 1,
            ],
            [
                'name' => 'Description',
                'code' => 'description',
                'type' => 'eav',
                'frontend' => 'wysiwyg',
                'model' => null,
                'admin_filter' => 0,
                'admin_column' => 0,
                'admin_validation' => null,
                'admin_massupdate' => 1,
            ],
            [
                'name' => 'Images',
                'code' => 'images',
                'type' => 'eav',
                'frontend' => 'gallery',
                'model' => 'file',
                'admin_filter' => 0,
                'admin_column' => 0,
                'admin_validation' => null,
                'admin_massupdate' => 0,
            ],
            [
                'name' => 'Meta Title',
                'code' => 'meta_title',
                'type' => 'eav',
                'frontend' => 'text',
                'model' => null,
                'admin_filter' => 0,
                'admin_column' => 0,
                'admin_validation' => null,
                'admin_massupdate' => 1,
            ],
            [
                'name' => 'Meta Keywords',
                'code' => 'meta_keywords',
                'type' => 'eav',
                'frontend' => 'text',
                'model' => null,
                'admin_filter' => 0,
                'admin_column' => 0,
                'admin_validation' => null,
                'admin_massupdate' => 1,
            ],
            [
                'name' => 'Meta Description',
                'code' => 'meta_description',
                'type' => 'eav',
                'frontend' => 'textarea',
                'model' => null,
                'admin_filter' => 0,
                'admin_column' => 0,
                'admin_validation' => null,
                'admin_massupdate' => 1,
            ],
            [
                'name' => 'Stock Quantity',
                'code' => 'stock_qty',
                'type' => 'eav',
                'frontend' => 'number',
                'model' => null,
                'admin_filter' => 0,
                'admin_column' => 0,
                'admin_validation' => 'integer|min:0',
                'admin_massupdate' => 1,
            ],
            [
                'name' => 'Stock Managed',
                'code' => 'stock_managed',
                'type' => 'eav',
                'frontend' => 'switch',
                'model' => null,
                'admin_filter' => 0,
                'admin_column' => 0,
                'admin_validation' => null,
                'admin_massupdate' => 1,
            ],
            [
                'name' => 'Stock Status',
                'code' => 'stock_status',
                'type' => 'virtual',
                'frontend' => 'stock',
                'model' => 'stock',
                'admin_filter' => 60,
                'admin_column' => 60,
                'admin_validation' => null,
                'admin_massupdate' => 0,
            ],
            [
                'name' => 'Weight',
                'code' => 'weight',
                'type' => 'eav',
                'frontend' => 'number',
                'model' => null,
                'admin_filter' => 0,
                'admin_column' => 0,
                'admin_validation' => 'min:0',
                'admin_massupdate' => 1,
            ],
            [
                'name' => 'Categories',
                'code' => 'category_ids',
                'type' => 'virtual',
                'frontend' => 'select-multiple',
                'model' => 'category',
                'admin_filter' => 50,
                'admin_column' => 0,
                'admin_validation' => null,
                'admin_massupdate' => 1,
            ],
            [
                'name' => 'Variation Attributes',
                'code' => 'variation_attributes',
                'type' => 'eav',
                'frontend' => 'variation_attributes',
                'model' => 'json',
                'admin_filter' => 0,
                'admin_column' => 0,
                'admin_validation' => null,
                'admin_massupdate' => 0,
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
        $this->_schema->drop('cc_product_attributes');
    }
}


<?php

namespace CoasterCommerce\Core\Model\Product\Attribute\OptionSource;

use CoasterCommerce\Core\Model\Product as ProductModel;

class Product implements OptionSourceInterface
{

    /**
     * @return array
     */
    public function optionsData()
    {
        $options = [];
        $products = (new ProductModel)->newModelQuery()->get(['id', 'name', 'sku']);
        foreach ($products as $product) {
            $options[$product->id] = '#' . $product->id . ' ' . $product->name . ' [' . $product->sku . ']';
        }
        return $options;
    }

}

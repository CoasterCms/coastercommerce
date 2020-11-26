<?php namespace CoasterCommerce\Core\Events;

use CoasterCommerce\Core\Model\Product;

class AdminProductSave
{

    /**
     * @var Product
     */
    public $product;

    /**
     * @var array
     */
    public $inputData;

    /**
     * AdminProductSave constructor.
     * @param Product $product
     * @param array $inputData
     */
    public function __construct(Product $product, array $inputData)
    {
        $this->product = $product;
        $this->inputData = $inputData;
    }

}


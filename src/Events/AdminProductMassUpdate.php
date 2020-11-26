<?php namespace CoasterCommerce\Core\Events;

use CoasterCommerce\Core\Model\Product;

class AdminProductMassUpdate
{

    /**
     * @var Product
     */
    public $products;

    /**
     * @var array
     */
    public $inputData;

    /**
     * AdminProductMassUpdate constructor.
     * @param Product[] $products
     * @param array $inputData
     */
    public function __construct($products, array $inputData)
    {
        $this->products = $products;
        $this->inputData = $inputData;
    }

}


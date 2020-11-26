<?php namespace CoasterCommerce\Core\Events;

use CoasterCommerce\Core\Model\Category;
use CoasterCommerce\Core\Model\Product;

class AdminCategorySave
{

    /**
     * @var Category
     */
    public $category;

    /**
     * @var array
     */
    public $inputData;

    /**
     * AdminCategorySave constructor.
     * @param Category $category
     * @param array $inputData
     */
    public function __construct(Category $category, array $inputData)
    {
        $this->category = $category;
        $this->inputData = $inputData;
    }

}


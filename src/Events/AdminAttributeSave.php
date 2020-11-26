<?php namespace CoasterCommerce\Core\Events;

use CoasterCommerce\Core\Model\Product\Attribute;

class AdminAttributeSave
{

    /**
     * @var Attribute
     */
    public $attribute;

    /**
     * @var array
     */
    public $inputData;

    /**
     * AdminAttributeSave constructor.
     * @param Attribute $attribute
     * @param array $inputData
     */
    public function __construct(Attribute $attribute, array $inputData)
    {
        $this->attribute = $attribute;
        $this->inputData = $inputData;
    }

}


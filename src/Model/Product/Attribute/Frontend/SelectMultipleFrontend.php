<?php

namespace CoasterCommerce\Core\Model\Product\Attribute\Frontend;

use CoasterCommerce\Core\Model\Product\Attribute;
use CoasterCommerce\Core\Model\Product\AttributeCache;
use Illuminate\View\View;

class SelectMultipleFrontend extends SelectFrontend
{

    protected $_filterView = 'select-multiple';

    protected $_inputView = 'select-multiple';

    /**
     * @param Attribute $attribute
     * @param array $value
     * @param int $id
     * @return string
     */
    public function dataTableCellValue($attribute, $value, $id)
    {
        $optionNames = $this->getOptionNames($attribute->code);
        $categories = array_filter(array_map(function ($selectedValue) use ($optionNames) {
            return array_key_exists($selectedValue, $optionNames)? $optionNames[$selectedValue] : null;
        }, $value));
        return implode(', ', $categories);
    }

    /**
     * @return string
     */
    public function defaultModel()
    {
        return 'json';
    }

}

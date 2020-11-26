<?php

namespace CoasterCommerce\Core\Model\Product\Attribute\Frontend;

use CoasterCommerce\Core\Model\Product\Attribute;

class SwitchFrontend extends AbstractFrontend
{

    /**
     * @param Attribute $attribute
     * @param string $value
     * @param int $id
     * @return string
     */
    public function dataTableCellValue($attribute, $value, $id)
    {
        return $value ? 'Yes' : 'No';
    }

}

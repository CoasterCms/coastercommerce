<?php

namespace CoasterCommerce\Core\Model\Product\Attribute\Frontend;

use CoasterCommerce\Core\Model\Product\Attribute;

class WysiwygFrontend extends TextareaFrontend
{

    protected $_inputView = 'wysiwyg';

    protected $_filterView = 'text';

    /**
     * @param Attribute $attribute
     * @param string $value
     * @param int $id
     * @return string
     */
    public function dataTableCellValue($attribute, $value, $id)
    {
        return htmlentities($value);
    }

}

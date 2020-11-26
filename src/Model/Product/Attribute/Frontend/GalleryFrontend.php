<?php

namespace CoasterCommerce\Core\Model\Product\Attribute\Frontend;

use CoasterCommerce\Core\Model\Product\Attribute;

class GalleryFrontend extends AbstractFrontend
{

    protected $_inputView = 'file';

    /**
     * @param Attribute $attribute
     * @return array
     */
    public function dataTableColumnConf($attribute)
    {
        return parent::dataTableColumnConf($attribute) + ['orderable' => false, 'searchable' => false];
    }

    /**
     * @param Attribute $attribute
     * @param Attribute\Model\FileModel\FileValue $value
     * @param int $id
     * @return string
     */
    public function dataTableCellValue($attribute, $value, $id)
    {
        $imgSrc = '';
        if ($value->fileData) {
            reset($value->fileData);
            $imgSrc = '<img src="' . key($value->fileData) . '" style="max-height: 100px; max-width: 150px;" />';
            $imgSrc = '<a href="' . key($value->fileData) . '" target="_blank">' . $imgSrc . '</a>';
        }
        return $imgSrc;
    }

    /**
     * @param Attribute $attribute
     * @return null
     */
    public function renderFilter($attribute)
    {
        return null;
    }

    /**
     * @return string
     */
    public function defaultModel()
    {
        return 'file';
    }

}

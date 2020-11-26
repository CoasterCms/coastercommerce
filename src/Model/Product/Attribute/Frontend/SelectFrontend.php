<?php

namespace CoasterCommerce\Core\Model\Product\Attribute\Frontend;

use CoasterCommerce\Core\Model\Product\Attribute;
use CoasterCommerce\Core\Model\Product\AttributeCache;
use Illuminate\View\View;

class SelectFrontend extends AbstractFrontend
{

    /**
     * @var array
     */
    protected $_optionValues = [];

    /**
     * @param Attribute $attribute
     * @param string $value
     * @param int $id
     * @return string
     */
    public function dataTableCellValue($attribute, $value, $id)
    {
        $optionNames = $this->getOptionNames($attribute->code);
        return array_key_exists($value, $optionNames)? $optionNames[$value] : null;
    }

    /**
     * @param Attribute $attribute
     * @param mixed $value
     * @return View
     */
    public function renderInput($attribute, $value)
    {
        return parent::renderInput($attribute, $value)->with([
            'options' => $this->getOptionNames($attribute->code)
        ]);
    }

    /**
     * @param Attribute $attribute
     * @return View
     */
    public function renderFilter($attribute)
    {
        return parent::renderFilter($attribute)->with([
            'options' => $this->getOptionNames($attribute->code)
        ]);
    }

    /**
     * @param string $attributeCode
     * @return mixed
     */
    public function getOptionNames($attributeCode)
    {
        if (!array_key_exists($attributeCode, $this->_optionValues)) {
            if ($metadata = AttributeCache::getMeta($attributeCode)) {
                if ($metadata->offsetExists('source')) {
                    $this->_optionValues[$attributeCode] = $this->_getFromOptionSource($metadata->offsetGet('source')->value);
                    if ($metadata->offsetExists('source-null')) {
                        $this->_optionValues[$attributeCode] = ['' => '-- None --'] + $this->_optionValues[$attributeCode];
                    }
                } else {
                    $this->_optionValues[$attributeCode] = $this->_getFromOptionsTable(
                        $metadata->offsetExists('options') ? $metadata->offsetGet('options')->value : null
                    );
                }
            } else {
                $this->_optionValues[$attributeCode] = [];
            }
            if ($metadata->offsetExists('ordered') && $metadata->offsetGet('ordered')) {
                asort($this->_optionValues[$attributeCode]);
            }
        }
        return $this->_optionValues[$attributeCode];
    }

    /**
     * @param string $optionsJson
     * @return array
     */
    protected function _getFromOptionsTable($optionsJson)
    {
        $options = [];
        if ($optionsData = json_decode($optionsJson)) {
            foreach ($optionsData as $optionData) {
                $options[$optionData->value] = $optionData->name;
            }
        }
        return $options;
    }

    /**
     * @param string $sourceClass
     * @return array
     */
    protected function _getFromOptionSource($sourceClass)
    {
        if (class_exists($sourceClass)) {
            /** @var Attribute\OptionSource\OptionSourceInterface $sourceModel */
            $sourceModel = new $sourceClass;
            return $sourceModel->optionsData();
        }
        return [];
    }

}

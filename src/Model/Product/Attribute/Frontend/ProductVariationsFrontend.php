<?php

namespace CoasterCommerce\Core\Model\Product\Attribute\Frontend;

class ProductVariationsFrontend extends TextFrontend
{

    /**
     * @param \CoasterCommerce\Core\Model\Product\Attribute $attribute
     * @param array $value
     * @return mixed
     */
    public function modifySubmittedData($attribute, $value)
    {
        if (is_array($value)) {
            $processedValue = [];
            foreach ($value as $variationAttribute) {
                $processedValue[$variationAttribute['attribute']] = [];
                if (array_key_exists('option', $variationAttribute) && is_array($variationAttribute['option'])) {
                    foreach ($variationAttribute['option'] as $option) {
                        $processedValue[$variationAttribute['attribute']][$option['value']] = array_diff_key($option, ['value' => '']);
                    }
                }
            }
        } else {
            $processedValue = [];
        }
        return parent::modifySubmittedData($attribute, $processedValue);
    }

}

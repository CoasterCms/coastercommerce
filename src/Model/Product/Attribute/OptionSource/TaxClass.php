<?php

namespace CoasterCommerce\Core\Model\Product\Attribute\OptionSource;

use CoasterCommerce\Core\Model\Tax\TaxClass as TaxClassModel;

class TaxClass implements OptionSourceInterface
{

    public function optionsData()
    {
        $options = [];
        foreach (TaxClassModel::all() as $taxClass) {
            $options[$taxClass->id] = $taxClass->name;
        }
        return $options;
    }

}

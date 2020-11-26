<?php

namespace CoasterCommerce\Core\Model\Product\Attribute\OptionSource;

use CoasterCommerce\Core\Model\Category as CategoryModel;

class Category implements OptionSourceInterface
{

    /**
     * @return array
     */
    public function optionsData()
    {
        $names = [];
        $categories = CategoryModel::all();
        foreach ($categories as $category) {
            $names[$category->id] = $category->name;
        }
        $options = [];
        foreach ($categories as $category) {
            $pathIds = explode('/', $category->fullPath());
            $options[$category->id] = implode(' » ', array_map(function ($pathId) use ($names) {
                return $names[$pathId];
            }, $pathIds));
        }
        asort($options);
        return $options;
    }

}

<?php

namespace CoasterCommerce\Core\Model\Product\Attribute;

use CoasterCommerce\Core\Model\Product;
use CoasterCommerce\Core\Model\Product\Attribute;
use CoasterCommerce\Core\Model\Product\Attribute\Frontend\AbstractFrontend;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;

class FrontendTypes
{

    /**
     * @var AbstractFrontend
     */
    protected $_types;

    /**
     * EavTypes constructor.
     * @param AbstractFrontend[] $types
     */
    public function __construct($types)
    {
        $this->_types = $types;
    }

    /**
     * @param Attribute $attribute
     * @return mixed
     */
    public function dataTableColumnConf($attribute)
    {
        return $this->_types[$attribute->frontend]->dataTableColumnConf($attribute);
    }

    /**
     * @param Attribute $attribute
     * @param string $value
     * @param int $id
     * @return mixed
     */
    public function dataTableCellValue($attribute, $value, $id)
    {
        return $this->_types[$attribute->frontend]->dataTableCellValue($attribute, $value, $id);
    }

    /**
     * @param Attribute $attribute
     * @param Product $product
     * @return View
     */
    public function renderInput($attribute, $product)
    {
        $value = $product->exists ? $product->getAttribute($attribute->code) : null;
        return $this->_types[$attribute->frontend]->renderInput($attribute, $value);
    }

    /**
     * @param Attribute $attribute
     * @return View
     */
    public function renderFilter($attribute)
    {
        return $this->_types[$attribute->frontend]->renderFilter($attribute);
    }

    /**
     * @param Attribute $attribute
     * @param mixed $value
     * @return mixed
     */
    public function modifySubmittedData($attribute, $value)
    {
        return $this->_types[$attribute->frontend]->modifySubmittedData($attribute, $value);
    }

    /**
     * @param Attribute $attribute
     * @param mixed $value
     * @return mixed
     */
    public function submissionRules($attribute, $value)
    {
        return $this->_types[$attribute->frontend]->submissionRules($attribute, $value);
    }

    /**
     * @param Attribute $attribute
     * @param mixed $filterValue
     * @param Builder $query
     * @return Builder
     */
    public function filterQuery($attribute, $filterValue, $query)
    {
        return $this->_types[$attribute->frontend]->filterQuery($attribute, $filterValue, $query);
    }

    /**
     * @return array
     */
    public function selectOptions()
    {
        $selectOptions = [];
        foreach ($this->_types as $type => $typeModel) {
            $selectOptions[$type] = $typeModel->name();
        }
        return $selectOptions;
    }

    /**
     * @param Attribute $attribute
     * @return string
     */
    public function defaultModel($attribute)
    {
        return $this->_types[$attribute->frontend]->defaultModel();
    }

}

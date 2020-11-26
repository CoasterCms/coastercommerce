<?php

namespace CoasterCommerce\Core\Model\Product\Attribute\Frontend;

use CoasterCommerce\Core\Model\DatatableState;
use CoasterCommerce\Core\Model\Product\Attribute;
use CoasterCommerce\Core\Model\Product\AttributeCache;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;

abstract class AbstractFrontend
{

    /**
     * @var string
     */
    protected $_filterView;

    /**
     * @var string
     */
    protected $_inputView;

    /**
     * @param Attribute $attribute
     * @return array
     */
    public function dataTableColumnConf($attribute)
    {
        return ['data' => $attribute->code, 'title' => $attribute->name];
    }

    /**
     * @param Attribute $attribute
     * @param mixed $value
     * @param int $id
     * @return string
     */
    public function dataTableCellValue($attribute, $value, $id)
    {
        return $value;
    }

    /**
     * @param Attribute $attribute
     * @param mixed $value
     * @return View
     */
    public function renderInput($attribute, $value)
    {
        $meta = AttributeCache::getMeta($attribute->code)->pluck('value', 'key')->toArray();
        $defaultValue = array_key_exists('default', $meta) ? $meta['default'] : null;
        $view = $attribute->view ?: // useful for creating dummy attributes to use on edit product page
            'coaster-commerce::admin.product.attribute-input.' . ($this->_inputView ?: $attribute->frontend);
        return view($view, [
            'attribute' => $attribute,
            'value' => is_null($value) ? $defaultValue : $value,
            'frontend' => $this,
            'meta' => $meta,
            'note' => array_key_exists('note', $meta) ? $meta['note'] : null
        ]);
    }

    /**
     * @param Attribute $attribute
     * @return View
     */
    public function renderFilter($attribute)
    {
        $tableState = DatatableState::loadUserState('product_list');
        return view('coaster-commerce::admin.product.attribute-filter.' . ($this->_filterView ?: $attribute->frontend), [
            'attribute' => $attribute,
            'frontend' => $this,
            'filterState' => $tableState ? $tableState->filterState($attribute->code) : null
        ]);
    }

    /**
     * @param Attribute $attribute
     * @param string $value
     * @return mixed
     */
    public function modifySubmittedData($attribute, $value)
    {
        return $value;
    }

    /**
     * @param Attribute $attribute
     * @param string $value
     * @return string
     */
    public function submissionRules($attribute, $value)
    {
        return $value;
    }

    /**
     * @param Attribute $attribute
     * @param mixed $filterValue
     * @param Builder $query
     * @return Builder
     */
    public function filterQuery($attribute, $filterValue, $query)
    {
        return $query->where($attribute->code, $filterValue);
    }

    /**
     * For select input type options
     * @return string
     */
    public function name()
    {
        $className = get_class($this);
        $shortClassName = substr($className, strrpos($className, '\\') + 1);
        return implode(' ', array_slice(preg_split('/(?=[A-Z])/', $shortClassName), 1, -1));
    }

    /**
     * Default model class to use with frontend class
     * @return null
     */
    public function defaultModel()
    {
        return null;
    }

}

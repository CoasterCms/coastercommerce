<?php

namespace CoasterCommerce\Core\Renderer\Admin;

use CoasterCommerce\Core\Model\Product\Attribute\OptionSource\Category;
use Illuminate\View\View;

/**
 * Helper to reuse product input views
 */
class Attribute
{

    /**
     * @var string
     */
    public $code;

    /**
     * @var string
     */
    public $frontend;

    /**
     * @var string
     */
    public $name;

    /**
     * @var array
     */
    public $meta;

    /**
     * @var string
     */
    protected $_key;

    /**
     * Attribute constructor.
     * @param string $code
     * @param string $type
     * @param string $name
     * @param array $meta
     */
    public function __construct($code, $type, $name, $meta = [])
    {
        $this->code = $code;
        $this->frontend = $type;
        $this->name = $name;
        $this->meta = $meta;
        $this->_key = 'attributes';
    }

    /**
     * @param mixed $value
     * @param array $viewData
     * @return View|string
     */
    public function renderInput($value = null, $viewData = [])
    {
        $view = $this->frontend;
        switch($this->frontend) {
            case 'category':
                $view = 'select';
                $viewData['options'] = [0 => '-- Root Category --'] + (new Category())->optionsData();
                break;
        }
        if (!view()->exists('coaster-commerce::admin.product.attribute-input.' . $view)) {
            return 'View not found: coaster-commerce::admin.product.attribute-input.' . $view;
        }
        return view('coaster-commerce::admin.product.attribute-input.' . $view, [
                'attribute' => $this,
                'value' => $value,
                'frontend' => $this,
                'meta' => $this->meta
            ] + $viewData);
    }

    /**
     * @param string $key
     * @return $this
     */
    public function key($key = null)
    {
        $this->_key = $key;
        return $this;
    }

    /**
     * @return string
     */
    public function id()
    {
        return $this->_key ? str_replace('.', '_', $this->_key) . '_' : null;
    }

    /**
     * @return string
     */
    public function fieldName()
    {
        if (strpos($this->_key, '.') !== false) {
            $key = preg_replace('/]/', '', str_replace('.', '][', $this->_key) . ']', 1);
        } else {
            $key = $this->_key;
        }
        return $key ? $key . '[' . $this->code . ']' : $this->code;
    }

    /**
     * @return string
     */
    public function fieldKey()
    {
        return $this->_key ? $this->_key . '.' . $this->code : $this->code;
    }

}

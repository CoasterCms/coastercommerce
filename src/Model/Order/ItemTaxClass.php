<?php

namespace CoasterCommerce\Core\Model\Order;

/**
 * Class ItemTaxClass
 * @package CoasterCommerce\Core\Model\Order\ItemTaxClass
 */
class ItemTaxClass
{

    /**
     * @var bool
     */
    protected $_init;

    /**
     * @var ItemTaxClassModifier[]|int[]
     */
    protected $_classes;

    /**
     * ItemTaxClass constructor.
     * @param ItemTaxClassModifier[]|int[] $classes
     */
    public function __construct($classes = [])
    {
        $this->_classes = $classes;
        $this->_init = false;
    }

    /**
     * @return string[]
     */
    public function getModifierClasses()
    {
        return array_keys($this->_classes);
    }

    /**
     * Keys should be class names, values should be sort order
     * @param int[] $classes
     */
    public function setModifierClasses($classes = [])
    {
        $this->_classes = $classes;
    }

    /**
     * Init modifier classes
     */
    protected function _init()
    {
        if (!$this->_init) {
            asort($this->_classes);
            foreach ($this->_classes as $class => $order) {
                $this->_classes[$class] = new $class;
            }
            $this->_init = true;
        }
    }

    /**
     * @param float $value
     * @param Item $item
     * @param string $fn
     * @return float
     */
    protected function _runModifiers($value, Item $item, $fn)
    {
        $this->_init(); // only init when required, saves additional processing on construct
        foreach ($this->_classes as $modifierClass) {
            $value = $modifierClass->$fn($value, $item);
        }
        return $value;
    }

    /**
     * @param int $defaultClass
     * @param Item $item
     * @return int
     */
    public function getClass($defaultClass, Item $item)
    {
        return $this->_runModifiers($defaultClass, $item, 'modifyClass');
    }

}

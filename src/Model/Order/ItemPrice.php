<?php

namespace CoasterCommerce\Core\Model\Order;

/**
 * Class ItemPrice
 * @package CoasterCommerce\Core\Model\Order\ItemPrice
 */
class ItemPrice
{

    /**
     * @var bool
     */
    protected $_init;

    /**
     * @var ItemPriceModifier[]|int[]
     */
    protected $_classes;

    /**
     * ItemPrice constructor.
     * @param ItemPriceModifier[]|int[] $classes
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
     * @param float $basePrice
     * @param Item $item
     * @return float
     */
    public function getPrice($basePrice, Item $item)
    {
        return $this->_runModifiers($basePrice, $item, 'modifyBasePrice');
    }

    /**
     * @param float $discount
     * @param Item $item
     * @return float
     */
    public function getDiscount($discount, Item $item)
    {
        return $this->_runModifiers($discount, $item, 'modifyDiscount');
    }

}

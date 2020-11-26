<?php

namespace CoasterCommerce\Core\Model\Order;

/**
 * Class ItemTaxClassModifier
 * @package CoasterCommerce\Core\Model\Order\ItemTaxClassModifier
 * Template class for modifying product/variation tax classes when adding to cart
 */
abstract class ItemTaxClassModifier
{

    /**
     * @param int $defaultClass
     * @param Item $item
     * @return int
     */
    public function modifyClass($defaultClass, Item $item)
    {
        return $defaultClass;
    }

}

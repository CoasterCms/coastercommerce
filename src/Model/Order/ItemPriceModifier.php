<?php

namespace CoasterCommerce\Core\Model\Order;

/**
 * Class ItemPriceModifier
 * @package CoasterCommerce\Core\Model\Order\ItemPriceModifier
 * Template class for modifying basket prices for items
 */
abstract class ItemPriceModifier
{

    /**
     * @param float $basePrice
     * @param Item $item
     * @return float
     */
    public function modifyBasePrice($basePrice, Item $item)
    {
        return $basePrice;
    }

    /**
     * @param float $discount
     * @param Item $item
     * @return float
     */
    public function modifyDiscount($discount, Item $item)
    {
        return $discount;
    }

}

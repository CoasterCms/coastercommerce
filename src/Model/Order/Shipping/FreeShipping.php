<?php

namespace CoasterCommerce\Core\Model\Order\Shipping;

/**
 * Class FreeShipping
 */
class FreeShipping extends AbstractShipping
{

    /**
     * @return float
     */
    public function rate()
    {
        return 0;
    }

}

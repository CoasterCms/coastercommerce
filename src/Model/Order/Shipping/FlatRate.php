<?php

namespace CoasterCommerce\Core\Model\Order\Shipping;

/**
 * Class FlatRate
 */

class FlatRate extends AbstractShipping
{

    /**
     * @return float
     */
    public function rate()
    {
        // get rate from data in custom_config field
        return (float) $this->getCustomField('fixed_rate') ?: 0;
    }

    /**
     * Renders custom settings in the admin
     * @return string
     */
    public function renderCustomFields()
    {
        return view('coaster-commerce::admin.shipping.method-flat', ['method' => $this]);
    }

}

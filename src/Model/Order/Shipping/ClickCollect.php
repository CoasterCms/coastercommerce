<?php

namespace CoasterCommerce\Core\Model\Order\Shipping;

/**
 * Class ClickCollect
 */

class ClickCollect extends FreeShipping
{
    // checkout logic in CoasterCommerce\Core\Listeners\OrderClickCollect

    /**
     * Renders custom settings in the admin
     * @return string
     */
    public function renderCustomFields()
    {
        return view('coaster-commerce::admin.shipping.method-clickcollect', ['method' => $this]);
    }

}

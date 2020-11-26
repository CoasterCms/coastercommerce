<?php namespace CoasterCommerce\Core\Listeners;

use CoasterCommerce\Core\Events\OrderPlaced as OrderPlacedEvent;
use CoasterCommerce\Core\Model\Promotion;

class OrderPlaced
{

    /**
     * @param OrderPlacedEvent $event
     */
    public function handle(OrderPlacedEvent $event)
    {
        $order = $event->order;
        // reduce coupon uses for used promotions
        foreach ($order->getAllActivePromotions() as $promotion) {
            $promotion->reduceCouponUses($order->order_coupon);
        }
        // reduce stock levels on product or variation
        foreach ($order->items as $item) {
            if ($item->variation && $item->variation->stock_qty > 0) {
                $item->variation->stock_qty = max(0, $item->variation->stock_qty - $item->item_qty);
                $item->variation->save();
            } elseif ($item->product && $item->product->stock_qty > 0) {
                $item->product->stock_qty = max(0, $item->product->stock_qty - $item->item_qty);
                $item->product->save();
            }
        }
    }

}


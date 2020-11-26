<?php
/**
 * @var CoasterCommerce\Core\Model\Order $order
 * @var \CoasterCommerce\Core\Model\Order\Payment\AbstractPayment $method
 */
?>

@if ($order->payment_confirmed)
    <p>Payment confirmed:<br /> {{ $order->payment_confirmed->format('H:i:s d/m/Y') }}</p>
@endif
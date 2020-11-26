<?php

namespace CoasterCommerce\Core\Model;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class AbandonedCart extends Model
{

    /**
     * @var string
     */
    protected $table = 'cc_abandoned_carts';

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    /**
     * Uses getAbandonedOrders() to populate the cc_abandoned_carts table so can track any cart updates
     * Returns all rows in cc_abandoned_carts with order relation
     * Hence may contain abandoned carts that have been subsequently completed
     * @return AbandonedCart[]|Collection
     */
    public static function updateAndReturnAbandonedCarts()
    {
        // load saved abandoned cart status data
        $abandonedCartsData = static::all()->keyBy('order_id');
        // load all abandoned carts from order table
        $orders = static::getAbandonedOrders();
        // update/add abandoned cart status data and attach order relation
        foreach ($orders as $order) {
            $abandonedCartData = $abandonedCartsData->offsetExists($order->id) ? $abandonedCartsData->offsetGet($order->id) : new static();
            $abandonedCartData->setRelation('order', $order);
            if (!$abandonedCartData->order_converted) {
                $email = $order->email;
                $addressDetails = $order->addresses->count();
                if (!$email && $customer = $order->customer) {
                    $email = $customer->email;
                    $addressDetails = $addressDetails ?: $customer->addresses->count();
                }
                if ($order->payment_method) {
                    $status = 'At Payment Gateway';
                } elseif ($order->shipping_method) {
                    $status = 'Customer & Shipping';
                } elseif ($addressDetails) {
                    $status = 'Customer Details';
                } else {
                    $status = 'Email Only';
                }
                $abandonedCartData->order_id = $order->id;
                $abandonedCartData->status = $status;
                $abandonedCartData->email = $email;
                $abandonedCartData->created_at = $abandonedCartData->created_at ?: $order->updated_at;
                $abandonedCartData->save();
                if ($abandonedCartData->wasRecentlyCreated) {
                    $abandonedCartsData->offsetSet($order->id, $abandonedCartData);
                }
            }
        }
        // update converted field in AbandonedCart model for orders that are now complete
        if ($possibleConvertedOrderIds = $abandonedCartsData->pluck('order_id')->diff($orders->pluck('id'))->toArray()) {
            if ($convertedOrderIds = Order::whereIn('id', $possibleConvertedOrderIds)->whereNotIn('order_status', static::_possibleAbandonedStatuses())->pluck('id')->toArray()) {
                static::whereIn('order_id', $convertedOrderIds)->update(['order_converted' => 1]); // quick single query db update
                foreach ($convertedOrderIds as $convertedOrderId) { // update already loaded models
                    $abandonedCartData = $abandonedCartsData->offsetGet($convertedOrderId);
                    $abandonedCartData->order_converted = 1;
                }
            }
        }
        return $abandonedCartsData;
    }

    /**
     * Loads any incomplete order than been left idle for at least 50mins and which has email email or some customer details attached
     * @return Order[]|Collection
     */
    public static function getAbandonedOrders()
    {
        return Order::with(['items', 'addresses', 'customer', 'customer.addresses'])
            ->whereIn('order_status', static::_possibleAbandonedStatuses())
            ->whereTime('updated_at', '<', Carbon::now()->modify('-50 minutes'))
            ->where('order_total_inc_vat', '>', 0)
            ->where(function (Builder $q) {
                $q->whereNotNull('customer_id')->orWhereNotNull('email');
            })->get();
    }

    /**
     * @return array
     */
    protected static function _possibleAbandonedStatuses()
    {
        return array_diff(Order\Status::quoteStatuses(), ['payment_gateway']); // payment_gateway orders are always clones so ignore
    }

}

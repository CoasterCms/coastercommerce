<?php
namespace CoasterCommerce\Core\Controllers\Api;

use CoasterCommerce\Core\Currency\Format;
use CoasterCommerce\Core\Model\AbandonedCart;
use CoasterCommerce\Core\Model\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Exception;

class AbandonedCartController extends Controller
{

    /**
     * @return JsonResponse
     * @throws Exception
     */
    public function getAdminList()
    {
        $abandonedCartColumns = [];
        $unsubscribed = AbandonedCart\Unsubscribed::all()->pluck('email')->toArray();
        $abandonedCarts = AbandonedCart::updateAndReturnAbandonedCarts();
        foreach ($abandonedCarts as $abandonedCart) {
            /** @var Order $order */
            $order = $abandonedCart->order;
            $abandonedCartColumns[] = [
                'id' => $abandonedCart->order_id,
                'email' => $abandonedCart->email,
                'date' => $abandonedCart->created_at->format('Y-m-d H:i'),
                'status' => $abandonedCart->status,
                'items' => $order->items->count(),
                'total' => (string) new Format($order->order_total_inc_vat),
                'email_last_sent' => $abandonedCart->email_last_sent,
                'emails_sent' => $abandonedCart->emails_sent,
                'converted' => $abandonedCart->order_converted ? 'Yes' : (in_array($abandonedCart->email, $unsubscribed) ? 'Unsubscribed' : 'No'),
            ];
        }
        return response()->json(['data' => $abandonedCartColumns]);
    }

}

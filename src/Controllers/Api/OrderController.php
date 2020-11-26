<?php
namespace CoasterCommerce\Core\Controllers\Api;

use CoasterCommerce\Core\Currency\Format;
use CoasterCommerce\Core\Model\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Exception;

class OrderController extends Controller
{

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function getAdminList(Request $request)
    {
        $orderColumns = [];
        $orders = Order::with(['addresses', 'status'])->whereIn('order_status', Order\Status::visibleStatuses());
        foreach ($request->post('filters', []) as $filter => $value) {
            is_array($value) ? $orders->whereIn($filter, $value) : $orders->where($filter, $value);
        }
        $orderCollection = $orders->get();
        foreach ($orderCollection as $order) {
            /** @var Order $order */
            $orderColumns[] = [
                'id' => $order->id,
                'number' => $order->order_number,
                'email' => $order->email,
                'name' => $order->billingAddress() ? $order->billingAddress()->first_name . ' ' . $order->billingAddress()->last_name : null,
                'total' => (string) new Format($order->order_total_inc_vat),
                'date_placed' => $order->order_placed ? $order->order_placed->format('Y-m-d H:i:s') : null,
                'paid' => ucwords($order->payment_method) . '&nbsp;' . ($order->payment_confirmed ? ' <span class="fa fa-check-circle text-success"></span>' : ' <span class="fa fa-times-circle text-danger"></span>'),
                'status' => $order->status ? $order->status->name : $order->order_status,
                'view' => '<a href="'.route('coaster-commerce.admin.order.view', ['id' => $order->id]).'">View</a>',
                'status_colour' => $order->status ? $order->status->colour : null
            ];
        }
        return response()->json(['data' => $orderColumns]);
    }

}

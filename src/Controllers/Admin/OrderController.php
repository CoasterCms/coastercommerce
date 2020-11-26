<?php
namespace CoasterCommerce\Core\Controllers\Admin;

use Carbon\Carbon;
use CoasterCommerce\Core\Mailables\OrderNoteMailable;
use CoasterCommerce\Core\Mailables\OrderShipmentMailable;
use CoasterCommerce\Core\Menu\AdminMenu;
use CoasterCommerce\Core\Model\Country;
use CoasterCommerce\Core\Model\Customer;
use CoasterCommerce\Core\Model\Order;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class OrderController extends AbstractController
{
    use ValidatesRequests;

    /**
     *
     */
    protected function _init()
    {
        /** @var AdminMenu $adminMenu */
        $adminMenu = app('coaster-commerce.admin-menu');
        $adminMenu->getByName('Orders')->setActive();
    }

    /**
     * @return View
     */
    public function list()
    {
        $this->_setTitle('Orders');
        return $this->_view('order.list', [
            'statuses' => Order\Status::getAllStatuses()->whereIn('code', Order\Status::visibleStatuses())->pluck('name', 'code')
        ]);
    }

    /**
     * @param int $id
     * @return View
     */
    public function view($id)
    {
        $this->_setTitle('View Order');
        if (!$order = Order::find($id)) {
            return $this->_notFoundView();
        }
        return $this->_view('order.view', [
            'statuses' => Order\Status::getAllStatuses()->whereIn('code', Order\Status::visibleStatuses())->pluck('name', 'code'),
            'order' => Order::with(['items', 'addresses'])->find($id)
        ]);
    }

    /**
     * @param Request $request
     * @param int $id
     * @return RedirectResponse
     * @throws ValidationException
     */
    public function saveStatus(Request $request, $id)
    {
        $this->validate($request, ['order_status' => 'exists:cc_order_status,code']);
        if ($order = Order::find($id)) {
            $order->order_status = $request->post('order_status');
            $order->save();
        }
        return $this->_redirectRoute('order.view', ['id' => $id]);
    }

    /**
     * @param int $id
     * @return RedirectResponse
     * @throws \Exception
     */
    public function savePaid($id)
    {
        $order = Order::find($id);
        if ($order && !$order->payment_confirmed) {
            $order->payment_confirmed = new Carbon();
            $order->order_status = ($order->shipment_confirmed || !$order->shipping_method) ? Order\Status::getDefaultStatus(Order::STATUS_COMPLETE)->code : Order\Status::getDefaultStatus(Order::STATUS_PROCESSING)->code;
            $order->save();
        }
        return $this->_redirectRoute('order.view', ['id' => $id]);
    }

    /**
     * @param Request $request
     * @param int $id
     * @return RedirectResponse
     * @throws \Exception
     */
    public function saveShipped(Request $request, $id)
    {
        $order = Order::find($id);
        if ($order && !$order->shipment_confirmed) {
            $order->shipment_sent = new Carbon();
            $order->order_status = $order->payment_confirmed ? Order\Status::getDefaultStatus(Order::STATUS_COMPLETE)->code : $order->order_status;
            $order->save();
            if ($number = $request->post('tracking_number')) {
                $shipment = new Order\ShipmentTracking();
                $shipment->number = $number;
                $shipment->courier_id = $request->post('tracking_courier');
                $shipment->order_id = $order->id;
                $shipment->save();
            }
            if ($request->post('send_email', false)) {
                $this->_flashAlert('success', 'Shipping email sent to customer');
                Mail::send(new OrderShipmentMailable($order));
            }
        }
        return $this->_redirectRoute('order.view', ['id' => $id]);
    }

    /**
     * @param int $id
     * @return RedirectResponse
     * @throws \Exception
     */
    public function emailOrder($id)
    {
        $order = Order::find($id);
        if ($order) {
            $order->sendEmail();
            $this->_flashAlert('success', 'Order email resent');
        }
        return $this->_redirectRoute('order.view', ['id' => $id]);
    }

    /**
     * @param Request $request
     * @param int $id
     * @return RedirectResponse
     * @throws \Exception
     */
    public function addNote(Request $request, $id)
    {
        $order = Order::find($id);
        if ($order && $noteContent = $request->post('note')) {
            $note = new Order\Note();
            $note->order_id = $id;
            $note->author = Auth::user()->email;
            $note->note = $noteContent;
            if ($request->post('notify')) {
                Mail::send(new OrderNoteMailable($note));
                $note->customer_notified = 1;
            }
            $note->save();
        }
        return $this->_redirectRoute('order.view', ['id' => $id]);
    }

    /**
     * @param int $id
     * @param string $type
     * @return View|RedirectResponse
     */
    public function editAddress($id, $type)
    {
        if ($address = Order\Address::where('order_id', $id)->where('type', $type)->first()) {
            return $this->_view('order.address-edit', [
                'address' => $address,
                'countries' => Country::names()
            ]);
        }
        return $this->_redirectRoute('order.view', ['id' => $id]);
    }

    /**
     * @param Request $request
     * @param int $id
     * @param string $type
     * @return RedirectResponse
     * @throws \Exception
     */
    public function updateAddress(Request $request, $id, $type)
    {
        $this->validate($request, (new Customer\Address())->validationRules('address.'));
        if ($address = Order\Address::where('order_id', $id)->where('type', $type)->first()) {
            /** @var Order\Address $address */
            $addressPostData = $request->post('address', []);
            $address->forceFill($addressPostData);
            $address->save();
            $this->_flashAlert('success', ucwords($type) . ' address updated');
        }
        return $this->_redirectRoute('order.view', ['id' => $id]);
    }

    /**
     * @param int $id
     * @return Response|RedirectResponse
     */
    public function getPdf($id)
    {
        if ($order = Order::find($id)) {
            $pdf = new Order\PDF($order);
            $pdf->addInvoiceSections();
            return $pdf->getPdf()->GenerateResponse('ORDER-'.str_replace('#', '', $order->order_number).'.pdf');
        }
        return $this->_redirectRoute('order.view', ['id' => $id]);
    }

}

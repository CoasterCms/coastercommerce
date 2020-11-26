<?php
namespace CoasterCommerce\Core\Controllers\Admin;

use CoasterCommerce\Core\Menu\AdminMenu;
use CoasterCommerce\Core\Model\Order;
use Illuminate\Contracts\View\View;

class AbandonedCartController extends AbstractController
{

    /**
     *
     */
    protected function _init()
    {
        /** @var AdminMenu $adminMenu */
        $adminMenu = app('coaster-commerce.admin-menu');
        $adminMenu->getByName('Customers')->setActive();
    }

    /**
     * @return View
     */
    public function list()
    {
        $this->_setTitle('Abandoned Carts');
        return $this->_view('customer.abandoned-cart.list');
    }

    /**
     * @param int $orderId
     * @return View
     */
    public function view($orderId)
    {
        if (!$order = Order::find($orderId)) {
            return $this->_notFoundView();
        }
        $email = $order->email;
        if (!$email && $customer = $order->customer) {
            $email = $customer->email;
        }
        $this->_setTitle('Cart for ' . $email);
        return $this->_view('customer.abandoned-cart.view', [
            'order' => $order,
            'email' => $email
        ]);
    }


}

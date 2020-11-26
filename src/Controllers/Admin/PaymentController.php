<?php
namespace CoasterCommerce\Core\Controllers\Admin;

use CoasterCommerce\Core\Menu\AdminMenu;
use CoasterCommerce\Core\Model\Order;
use CoasterCommerce\Core\Model\Order\Payment;
use CoasterCommerce\Core\Validation\ValidatesInput;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PaymentController extends AbstractController
{

    use ValidatesInput;

    /**
     *
     */
    protected function _init()
    {
        /** @var AdminMenu $adminMenu */
        $adminMenu = app('coaster-commerce.admin-menu');
        $adminMenu->getByName('Settings')->setActive();
    }

    /**
     * @return View
     */
    public function manage()
    {
        $processingStatues = \CoasterCommerce\Core\Model\Order\Status::getAllStatuses()
            ->where('state', Order::STATUS_PROCESSING)->pluck('name', 'code');
        return $this->_view('payment.manage', [
            'methods' => (new Payment())->getMethods(),
            'statuses' => $processingStatues
        ]);
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function save(Request $request)
    {
        $methods = (new Payment())->getMethods();
        $defaultFields = array_fill_keys(['active', 'name', 'description', 'sort_value', 'order_status', 'min_cart_total', 'max_cart_total'], null);
        foreach ($methods as $method) {
            if ($methodPostData = $request->post($method->code)) {
                $method->fillCustomFields(array_diff_key($methodPostData, $defaultFields));
                $method->getModel()
                    ->forceFill(array_intersect_key($methodPostData, $defaultFields))
                    ->save();
            }
        }

        $this->_flashAlert('success', 'Payment methods updated');
        return $this->_redirectRoute('system.payment');
    }

}

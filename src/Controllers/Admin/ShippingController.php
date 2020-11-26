<?php
namespace CoasterCommerce\Core\Controllers\Admin;

use Carbon\Carbon;
use CoasterCommerce\Core\Menu\AdminMenu;
use CoasterCommerce\Core\Model\Order\Shipping;
use CoasterCommerce\Core\Validation\ValidatesInput;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use League\Csv\CannotInsertRecord;
use League\Csv\Writer;

class ShippingController extends AbstractController
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
        return $this->_view('shipping.manage', [
            'methods' => (new Shipping())->getMethods()
        ]);
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function save(Request $request)
    {
        $methods = (new Shipping())->getMethods();
        $defaultFields = array_fill_keys(['active', 'name', 'description', 'sort_value', 'min_cart_total', 'max_cart_total'], null);
        foreach ($methods as $method) {
            if ($methodPostData = $request->post($method->code)) {
                $method->fillCustomFields(array_diff_key($methodPostData, $defaultFields));
                foreach ($method->getAlerts() as $class => $msg) {
                    $this->_flashAlert($class, $msg);
                }
                $method->getModel()
                    ->forceFill(array_intersect_key($methodPostData, $defaultFields))
                    ->save();
            }
        }

        $this->_flashAlert('success', 'Shipping methods updated');
        return $this->_redirectRoute('system.shipping');
    }

    /**
     * @param string $method
     * @return Response
     * @throws CannotInsertRecord
     */
    public function tableRates($method)
    {
        $handle = fopen('php://temp', 'w');
        $csv = Writer::createFromStream($handle);
        $csv->insertOne(Shipping\TableRate\Model::getRateHeaders());
        $csv->insertAll(Shipping\TableRate\Model::getRates($method));
        return response()->streamDownload(function () use($handle) {
            echo stream_get_contents($handle, -1, 0);
        }, 'table-rates-' . (new Carbon())->format('Y-m-d-H-i-s') . '.csv');
    }

}

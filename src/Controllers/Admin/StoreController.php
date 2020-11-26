<?php
namespace CoasterCommerce\Core\Controllers\Admin;

use CoasterCommerce\Core\Menu\AdminMenu;
use CoasterCommerce\Core\Model\Setting;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class StoreController extends AbstractController
{

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
    public function list()
    {
        $this->_setTitle('Store Details');
        return $this->_view('store.details', [
            'settings' => Setting::where('setting', 'LIKE', 'store_%')->get(),
        ]);
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function save(Request $request)
    {
        $inputData = $request->post();
        foreach ($inputData as $setting => $value) {
            if (stripos($setting,'store_') === 0) {
                (new Setting)->setValue($setting, $value);
            }
        }
        $this->_flashAlert('success', 'Store details updated!');
        return $this->_redirectRoute('system.store');
    }

}

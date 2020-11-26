<?php
namespace CoasterCommerce\Core\Controllers\Admin;

use CoasterCommerce\Core\Menu\AdminMenu;
use CoasterCommerce\Core\Model\Customer\Group;
use CoasterCommerce\Core\Model\Setting;
use CoasterCommerce\Core\Model\Tax\TaxClass;
use CoasterCommerce\Core\Model\Tax\TaxRule;
use CoasterCommerce\Core\Model\Tax\TaxZone;
use CoasterCommerce\Core\Model\Tax\TaxZoneArea;
use CoasterCommerce\Core\Validation\ValidatesInput;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class VatController extends AbstractController
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
    public function overview()
    {
        return $this->_view('vat.overview', [
            'taxClasses' => TaxClass::all(),
            'taxZones' => TaxZone::all(),
            'taxRules' => TaxRule::with(['taxClass', 'taxZone', 'customerGroup'])->get()
        ]);
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function settingsSave(Request $request)
    {
        $settings = [
            'vat_catalogue_display',
            'vat_catalogue_price',
            'vat_catalogue_discount_calculation',
            'vat_shipping_price',
            'vat_cart_discount_calculation',
            'vat_calculate_on',
            'vat_tax_class',
            'vat_tax_zone'
        ];
        foreach ($settings as $setting) {
            $value = $request->post($setting);
            if (!is_null($value)) {
                (new Setting())->setValue($setting, $value);
            }
        }
        $this->_flashAlert('success', 'Setting updated');
        return $this->_redirectRoute('system.vat');
    }

    /**
     * @param int $id
     * @return View|RedirectResponse
     */
    public function classEdit($id = null)
    {
        $taxClass = $id ? (new TaxClass())->where('id', $id)->first() : new TaxClass();
        if ($taxClass) {
            return $this->_view('vat.class', ['taxClass' => $taxClass]);
        }
        return $this->_redirectRoute('system.vat');
    }

    /**
     * @param Request $request
     * @param int $id
     * @return View|RedirectResponse
     * @throws ValidationException
     */
    public function classSave(Request $request, $id = null)
    {
        $this->validate($request->post(), ['name' => 'required']);
        $taxClass = $id ? (new TaxClass())->where('id', $id)->first() : new TaxClass();
        $taxClass->name = $request->post('name');
        $taxClass->save();
        $this->_flashAlert('success', 'Tax class "' . $taxClass->name . '" saved.');
        return $request->post('saveAction') == 'continue' ?
            $this->_redirectRoute('system.vat.class.edit', ['id' => $taxClass->id]) :
            $this->_redirectRoute('system.vat');
    }

    /**
     * @param int $id
     * @return RedirectResponse
     */
    public function classDelete($id)
    {
        if ($taxClass = (new TaxClass())->where('id', $id)->first()) {
            try {
                $taxClass->delete();
                $this->_flashAlert('success', 'Tax class "' . $taxClass->name . '" removed');
            } catch (\Exception $e) {
                $this->_flashAlert('danger', 'Error deleting tax class, there may still be products with it assigned.');
            }
        }
        return $this->_redirectRoute('system.vat');
    }

    /**
     * @param int $id
     * @return View|RedirectResponse
     */
    public function zoneEdit($id = null)
    {
        $taxZone = $id ? (new TaxZone())->where('id', $id)->first() : new TaxZone();
        if ($taxZone) {
            return $this->_view('vat.zone', ['taxZone' => $taxZone]);
        }
        return $this->_redirectRoute('system.vat');
    }

    /**
     * @param Request $request
     * @param int $id
     * @return View|RedirectResponse
     * @throws ValidationException
     */
    public function zoneSave(Request $request, $id = null)
    {
        $this->validate($request->post(), ['name' => 'required']);
        // save zone
        $taxZone = $id ? (new TaxZone())->where('id', $id)->first() : new TaxZone();
        $taxZone->name = $request->post('name');
        $taxZone->save();
        // save zone areas
        $areas = $request->post('areas', []);
        TaxZoneArea::where('tax_zone_id', $taxZone->id)->delete();
        foreach ($areas as $area) {
            $areaModel = new TaxZoneArea();
            $areaModel->country_iso3 = $area;
            $taxZone->areas()->save($areaModel);
        }
        // return
        $this->_flashAlert('success', 'Tax zone "' . $taxZone->name . '" saved.');
        return $request->post('saveAction') == 'continue' ?
            $this->_redirectRoute('system.vat.zone.edit', ['id' => $taxZone->id]) :
            $this->_redirectRoute('system.vat');
    }

    /**
     * @param int $id
     * @return RedirectResponse
     */
    public function zoneDelete($id)
    {
        if ($taxZone = (new TaxZone())->where('id', $id)->first()) {
            try {
                $taxZone->delete();
                $this->_flashAlert('success', 'Tax zone "' . $taxZone->name . '" removed');
            } catch (\Exception $e) {
                $this->_flashAlert('danger', 'Error deleting tax zone, there may still be products with it assigned.');
            }
        }
        return $this->_redirectRoute('system.vat');
    }

    /**
     * @param int $id
     * @return View|RedirectResponse
     */
    public function ruleEdit($id = null)
    {
        $taxRule = $id ? (new TaxRule())->where('id', $id)->first() : new TaxRule();
        if ($taxRule) {
            return $this->_view('vat.rule', [
                'taxRule' => $taxRule,
                'taxClasses' => TaxClass::pluck('name', 'id')->toArray(),
                'taxZones' => TaxZone::pluck('name', 'id')->toArray(),
                'customerGroups' => Group::pluck('name', 'id')->toArray()
            ]);
        }
        return $this->_redirectRoute('system.vat');
    }

    /**
     * @param Request $request
     * @param int $id
     * @return View|RedirectResponse
     * @throws ValidationException
     */
    public function ruleSave(Request $request, $id = null)
    {
        $this->validate($request->post(), [
            'name' => 'required',
            'tax_class_id' => 'required|integer',
            'tax_zone_id' => 'required|integer',
            'customer_group_id' => 'integer|nullable',
            'priority' => 'required|numeric',
            'percentage' => 'required|numeric'
        ]);
        $taxRule = $id ? (new TaxRule())->where('id', $id)->first() : new TaxRule();
        $taxRule->forceFill($request->only(['name', 'tax_class_id', 'tax_zone_id', 'customer_group_id', 'priority', 'percentage']));
        $taxRule->save();
        $this->_flashAlert('success', 'Tax rule "' . $taxRule->name . '" saved.');
        return $request->post('saveAction') == 'continue' ?
            $this->_redirectRoute('system.vat.rule.edit', ['id' => $taxRule->id]) :
            $this->_redirectRoute('system.vat');
    }

    /**
     * @param int $id
     * @return RedirectResponse
     */
    public function ruleDelete($id)
    {
        if ($taxRule = (new TaxRule())->where('id', $id)->first()) {
            try {
                $taxRule->delete();
                $this->_flashAlert('success', 'Tax rule "' . $taxRule->name . '" removed');
            } catch (\Exception $e) {
                $this->_flashAlert('danger', 'Error deleting tax rule, there may still be products with it assigned.');
            }
        }
        return $this->_redirectRoute('system.vat');
    }

}

<?php
namespace CoasterCommerce\Core\Controllers\Admin;

use CoasterCommerce\Core\Events\AdminCustomerGroupSave;
use CoasterCommerce\Core\Events\AdminCustomerSave;
use CoasterCommerce\Core\Menu\AdminMenu;
use CoasterCommerce\Core\Model\Country;
use CoasterCommerce\Core\Model\Customer;
use CoasterCommerce\Core\Model\Setting;
use CoasterCommerce\Core\Validation\ValidatesInput;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use League\ISO3166\ISO3166;

class CustomerController extends AbstractController
{
    use ValidatesInput;

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
        $this->_setTitle('Customers');
        return $this->_view('customer.list');
    }

    /**
     * @return View
     */
    public function add()
    {
        $this->_setTitle('New Customer');
        return $this->_view('customer.edit', [
            'customer' => new Customer,
            'groups' => Customer\Group::pluck('name', 'id')->toArray(),
            'countries' => Country::names()
        ]);

    }

    /**
     * @param int $id
     * @return View
     */
    public function edit($id)
    {
        $this->_setTitle('Edit Customer');
        if (!$customer = Customer::find($id)) {
            return $this->_notFoundView();
        }
        return $this->_view('customer.edit', [
            'customer' => Customer::with(['meta', 'addresses'])->find($id),
            'groups' => Customer\Group::pluck('name', 'id')->toArray(),
            'countries' => Country::names()
        ]);
    }

    /**
     * @param Request $request
     * @param Hasher $hasher
     * @param int $id
     * @return RedirectResponse|View
     * @throws ValidationException
     */
    public function save(Request $request, Hasher $hasher, $id)
    {
        if ($id) {
            if (!$customer = Customer::find($id)) {
                return $this->_notFoundView();
            }
        } else {
            $customer = new Customer();
        }
        $inputData = $request->post('attributes');
        // validate attribute inputData
        $rules = [];
        $niceNames = [];
        foreach (['email', 'group_id', 'password'] as $attribute) {
            $rules['attributes.' . $attribute] = 'required';
            $niceNames['attributes.' . $attribute] = strtolower($attribute);
        }
        $rules['attributes.email'] .= '|email';
        if (!$customer->exists || ($inputData['password'] !== '' && !is_null($inputData['password']))) {
            $rules['attributes.password'] .= '|min:6';
        } else {
            unset($inputData['password']);
            unset($rules['attributes.password']);
        }
        $this->validate(['attributes' => $inputData], $rules, [], $niceNames);
        if (array_key_exists('password', $inputData)) {
            $inputData['password'] = $hasher->make($inputData['password']);
        }
        // save inputData to customer model
        $customer
            ->forceFill(array_intersect_key($inputData, array_fill_keys(Schema::getColumnListing((new Customer)->getTable()), null)))
            ->save();
        // save non customer model data (ie. address/meta)
        event(new AdminCustomerSave($customer, $inputData));
        // redirect based on save action
        $this->_flashAlert('success', 'Customer "' . $customer->email . '" saved!');
        return $request->post('saveAction') == 'continue' ?
            $this->_redirectRoute('customer.edit', ['id' => $customer->id]) :
            $this->_redirectRoute('customer.list');
    }

    /**
     * @param int $id
     * @return RedirectResponse
     */
    public function delete($id)
    {
        if ($customer = Customer::find($id)) {
            if ($customer->delete()) {
                $this->_flashAlert('success', 'Customer "' . $customer->email . '" deleted!');
            }
        }
        return $this->_redirectRoute('customer.list');
    }

    /**
     * @return View
     */
    public function groupList()
    {
        $this->_setTitle('Customer Groups');
        return $this->_view('customer.group.list', [
            'groups' => Customer\Group::all(),
        ]);
    }

    /**
     * @return View
     */
    public function groupAdd()
    {
        $this->_setTitle('New Group');
        return $this->_view('customer.group.edit', [
            'group' => new Customer\Group()
        ]);

    }

    /**
     * @param int $id
     * @return View
     */
    public function groupEdit($id)
    {
        $this->_setTitle('Edit Group');
        if (!$group = Customer\Group::find($id)) {
            return $this->_notFoundView();
        }
        return $this->_view('customer.group.edit', [
            'group' => $group
        ]);
    }

    /**
     * @param Request $request
     * @param int $id
     * @return RedirectResponse|View
     * @throws ValidationException
     */
    public function groupSave(Request $request, $id)
    {
        if ($id) {
            if (!$group = Customer\Group::find($id)) {
                return $this->_notFoundView();
            }
        } else {
            $group = new Customer\Group();
        }
        $inputData = $request->post('attributes');
        // validate attribute inputData
        $rules = [];
        $niceNames = [];
        foreach (['name'] as $attribute) {
            $rules['attributes.' . $attribute] = 'required';
            $niceNames['attributes.' . $attribute] = strtolower($attribute);
        }
        $this->validate(['attributes' => $inputData], $rules, [], $niceNames);
        // save inputData to category model
        $group
            ->forceFill(array_intersect_key($inputData, array_fill_keys(Schema::getColumnListing((new Customer\Group)->getTable()), null)))
            ->save();
        // save non customer group model data
        event(new AdminCustomerGroupSave($group, $inputData));
        // redirect based on save action
        $this->_flashAlert('success', 'Customer group "' . $group->name . '" saved!');
        return $request->post('saveAction') == 'continue' ?
            $this->_redirectRoute('customer.group.edit', ['id' => $group->id]) :
            $this->_redirectRoute('customer.group.list');
    }

    /**
     * @param int $id
     * @return RedirectResponse
     */
    public function groupDelete($id)
    {
        if ($group = Customer\Group::find($id)) {
            if ($group->delete()) {
                $this->_flashAlert('success', 'Customer group "' . $group->name . '" deleted!');
            }
        }
        return $this->_redirectRoute('customer.group.list');
    }

    /**
     * @return View
     */
    public function countriesEdit()
    {
        $allCountries = [];
        foreach ((new ISO3166)->all() as $country) {
            $allCountries[$country['alpha3']] = $country['name'];
        }
        $countries = Country::pluck('iso3')->toArray();
        if (Setting::getValue('country_rule') == 'except') {
            $exceptCountries = [];
            foreach ($allCountries as $iso3 => $name) {
                if (!in_array($iso3, $countries)) {
                    $exceptCountries[] = $iso3;
                }
            }
            $countries = $exceptCountries;
        }
        return $this->_view('customer.countries', [
            'countries' => $countries,
            'allCountries' => $allCountries
        ]);
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function countriesSave(Request $request)
    {
        (new Setting)->setValue('country_default', $request->post('defaultCountry'));
        (new Setting)->setValue('country_rule', $request->post('rule'));
        $countries = $request->post('countries', []);
        if ($request->post('rule') == 'except') {
            $allowedCountries = [];
            foreach ((new ISO3166)->all() as $country) {
                if (!in_array($country['alpha3'], $countries)) {
                    $allowedCountries[] = $country['alpha3'];
                }
            }
            $countries = $allowedCountries;
        }
        Country::whereNotIn('iso3', $countries)->delete();
        $existing = Country::pluck('iso3')->toArray();
        foreach ($countries as $country) {
            if (!in_array($country, $existing)) {
                $newCountry = new Country();
                $newCountry->iso3 = $country;
                $newCountry->save();
            }
        }
        return $this->_redirectRoute('customer.countries');
    }

}

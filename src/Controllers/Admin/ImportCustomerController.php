<?php
namespace CoasterCommerce\Core\Controllers\Admin;

use CoasterCommerce\Core\Mailables\CustomerImportMailable;
use CoasterCommerce\Core\Model\Setting;
use Illuminate\Contracts\Hashing\Hasher;
use CoasterCommerce\Core\Model\Customer;
use CoasterCommerce\Core\Menu\AdminMenu;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use League\Csv\Reader;

class ImportCustomerController extends AbstractController
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
     * @param Request $request
     * @param Hasher $hasher
     * @return RedirectResponse
     */
    public function upload(Request $request, Hasher $hasher)
    {
        $path = $request->file('import-csv');
        $csv = Reader::createFromPath($path)->setHeaderOffset(0);
        $customers = Customer::with('addresses')->get()->keyBy('email');

        // key by lower case email
        $customers_array = [];
        foreach ($customers as $email => $customer) {
            $customers_array[strtolower($email)] = $customer;
        }
        $customers = new Collection($customers_array);

        $customers_updated = 0;
        $customers_added = 0;

        foreach ($csv as $record) {

            // basic validation
            $validator = Validator::make($record, [
                'email' => 'required|email'
            ]);
            if ($validator->fails()) {
                continue;
            }

            // get existing customer model or create new model
            if ($customers->offsetExists(strtolower($record['email']))) {
                ++$customers_updated;
                $customer = $customers->offsetGet(strtolower($record['email']));
            } else {
                ++$customers_added;
                $customer = new Customer;
                $customers->put($record['email'], $customer);
            }

            // add password for new customers with no password in csv
            $newPassword = null;
            if (!$customer->password && (!array_key_exists('password', $record) || !$record['password'])) {
                $newPassword = str_random(8);
                $record['password'] = $hasher->make($newPassword);
            }

            // save customer table & set default group if non existent
            $customerTableFields = [
                'email',
                'group_id',
                'password',
                'last_login',
                'created_at'
            ];
            $customerTableRecordFill = array_intersect_key($record, array_fill_keys($customerTableFields,null));
            $customerTableRecordFill['group_id'] = !empty($customerTableRecordFill['group_id']) ? $customerTableRecordFill['group_id'] : Setting::getValue('customer_default_group');
            $customer->forceFill($customerTableRecordFill)->save();
            $record = array_diff_key($record, array_fill_keys($customerTableFields,null)); // remove imported customer fields ($record used for custom data later)

            // get multiple address data
            $addresses = [];
            foreach ($record as $field => $value) {
                if (preg_match('/address.(\w+).(\w+)/', $field, $matches)) {
                    $addresses[$matches[1]][$matches[2]] = $value;
                    unset($record[$field]); // remove imported customer fields ($record used for custom data later)
                }
            }

            // get single address data
            if (!$addresses) {
                $singleAddressesFields = [
                    'first_name',
                    'last_name',
                    'company',
                    'address_line_1',
                    'address_line_2',
                    'town',
                    'county',
                    'country_iso2',
                    'country_iso3',
                    'postcode',
                    'phone'
                ];
                if ($address = array_intersect_key($record, array_fill_keys($singleAddressesFields,null))) {
                    $address['default_billing'] = 1;
                    $address['default_shipping'] = 1;
                    $addresses[1] = $address;
                }
                $record = array_diff_key($record, array_fill_keys($singleAddressesFields,null)); // remove imported customer fields ($record used for custom data later)
            }

            // save addresses
            foreach ($addresses as $address) {
                $this->_addAddress($customer, $address);
            }

            // add customer field, should be all remaining fields
            $customFields = $record;
            foreach ($customFields as $customField => $value) {
                DB::table('cc_customer_meta')->updateOrInsert([
                        'customer_id' => $customer->id,
                        'key' => $customField,
                    ], [
                        'value' => $value
                ]);
            }

            // send email to customer if password was randomly generated
            if (!is_null($newPassword) && Setting::getValue('import_emails_enabled')) {
                $this->sendEmail($record['email'], $newPassword);
            }

        }

        count($customers) > 0 ? $this->_flashAlert('success', $customers_added.' customers added and '.$customers_updated.' customers updated successfully!') 
                              : $this->_flashAlert('failed', 'No customers were imported.');

        return back();
    }

    /**
     * @param Customer $customer
     * @param array $addressRecord
     */
    protected function _addAddress($customer, $addressRecord)
    {
        // ignore address if it doesn't have require fields
        $isValid = false;
        $mustHaveA = ['first_name', 'company'];
        foreach ($mustHaveA as $field) {
            if (array_key_exists($field, $addressRecord) && $addressRecord[$field]) {
                $isValid = true;
                break;
            }
        }
        if (!$isValid) {
            return;
        }

        // convert iso2 to iso3
        if (array_key_exists('country_iso2', $addressRecord)) {
            try {
                $country = (new \League\ISO3166\ISO3166)->alpha2($addressRecord['country_iso2']);
                $addressRecord['country_iso3'] = $country['alpha3'];
            } catch (\Exception $e) {}
        }

        // fill missing records with blanks
        $addressRecord += array_fill_keys([
            'first_name',
            'last_name',
            'company',
            'address_line_1',
            'address_line_2',
            'town',
            'county',
            'country_iso3',
            'postcode',
            'phone',
            'email',
            'default_billing',
            'default_shipping'
        ], null);

        // create/get address model
        if ($addressRecord['default_billing']) {
            $address = $customer->defaultBillingAddress();
        } elseif ($addressRecord['default_billing']) {
            $address = $customer->defaultShippingAddress();
        } else {
            $address = new Customer\Address();
        }

        $address->forceFill([
            'customer_id' => $customer->id,
            'first_name' => $addressRecord['first_name'] ?: ucwords($addressRecord['company']),
            'last_name' => $addressRecord['last_name'] ?: '',
            'company' => ucwords($addressRecord['company']) ?: '',
            'address_line_1' => ucwords($addressRecord['address_line_1']) ?: '',
            'address_line_2' => ucwords($addressRecord['address_line_2']) ?: null,
            'town' => ucwords($addressRecord['town']) ?: '',
            'county' => ucwords($addressRecord['county']) ?: null,
            'country_iso3' => $addressRecord['country_iso3'] ?: Setting::getValue('country_default'),
            'postcode' => $addressRecord['postcode'] ?: '',
            'phone' => $addressRecord['phone'] ?: null,
            'email' => $addressRecord['email'] ?: null,
            'default_billing' => !!$addressRecord['default_billing'],
            'default_shipping' => !!$addressRecord['default_shipping']
        ])->save();
    }

    /**
     * @param string $email
     * @param string $password
     */
    private function sendEmail($email, $password)
    {
        Mail::to($email)->send(new CustomerImportMailable($password, $email));
    }

    /**
     * @return View
     */
    public function customers()
    {
        $this->_setTitle('Customer Import');
        return $this->_view('import.customers');
    }


}

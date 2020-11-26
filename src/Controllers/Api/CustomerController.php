<?php
namespace CoasterCommerce\Core\Controllers\Api;

use CoasterCommerce\Core\Model\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Exception;

class CustomerController extends Controller
{

    /**
     * @return JsonResponse
     * @throws Exception
     */
    public function getAdminList()
    {
        $customerColumns = [];
        $customers = Customer::with(['addresses', 'group'])->get();
        foreach ($customers as $customer) {
            $address = $customer->defaultBillingAddress();
            $address = !$address->exists && $customer->addresses->first() ? $customer->addresses->first() : $address;
            /** @var Customer $customer */
            $customerColumns[] = [
                'id' => $customer->id,
                'name' => $address->fullName(),
                'company' => $address->company,
                'group' => $customer->group->name,
                'email' => $customer->email,
                'country' => $address->country(),
                'last_login' => $customer->last_login ? $customer->last_login->format('Y-m-d H:i:s') : null,
                'created_at' => $customer->created_at->format('Y-m-d H:i:s')
            ];
        }
        return response()->json(['data' => $customerColumns]);
    }

}

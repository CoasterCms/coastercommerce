<?php
namespace CoasterCommerce\Core\Controllers\Frontend;

use Carbon\Carbon;
use CoasterCommerce\Core\Events\FrontendInit;
use CoasterCommerce\Core\Menu\FrontendCrumb;
use CoasterCommerce\Core\Menu\FrontendItem;
use CoasterCommerce\Core\Model\Customer;
use CoasterCommerce\Core\Model\Order;
use CoasterCommerce\Core\Model\Setting;
use CoasterCommerce\Core\ReCaptcha;
use Illuminate\Contracts\Auth\PasswordBrokerFactory;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Contracts\View\View;
use Illuminate\Cookie\CookieJar;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Validator;

class CustomerController extends AbstractController
{
    use ValidatesRequests;

    /**
     * Sets breadcrumb, metas and active menu item
     * @param string $title
     * @param string $desc
     * @param string $keywords
     */
    protected function _setCustomerMeta($title, $desc = null, $keywords = null)
    {
        $this->_setCustomerMenuActiveItem($title);
        $customerCrumb = new FrontendCrumb('Account', route('coaster-commerce.frontend.customer.account'));
        event(new FrontendInit(
            app('coaster-commerce.url-resolver')->setCustomPage([$customerCrumb, new FrontendCrumb($title, null)], $title, $desc, $keywords)
        ));
    }

    /**
     * @param string $name
     */
    protected function _setCustomerMenuActiveItem($name)
    {
        /** @var FrontendItem $item */
        if ($item = app('coaster-commerce.customer-menu')->getByName($name)) {
            $item->setActive();
        }
    }

    /**
     * @return View
     */
    public function details()
    {
        $this->_setCustomerMeta('Account Details');

        return $this->_view('customer.account.details');
    }

    /**
     * @return View
     */
    public function addressNew()
    {
        $this->_setCustomerMeta('New Address');
        $this->_setCustomerMenuActiveItem('Account Details');
        return $this->_view('customer.account.address', ['address' => new Customer\Address()]);
    }

    /**
     * @param int $id
     * @return View|RedirectResponse
     */
    public function addressEdit($id)
    {
        if (!($address = Customer\Address::where('customer_id', $this->_cart->getCustomerId())->find($id))) {
            return $this->_redirect('customer.account.address');
        }
        $this->_setCustomerMeta('Edit Address');
        $this->_setCustomerMenuActiveItem('Address Details');
        return $this->_view('customer.account.address', ['address' => $address]);
    }

    /**
     * @param Request $request
     * @param int $id
     * @return RedirectResponse
     * @throws ValidationException
     */
    public function addressSave(Request $request, $id)
    {
        $this->validate($request, (new Customer\Address())->validationRules());

        $customerId = $this->_cart->getCustomerId();

        if ($request->default_billing) {
            Customer\Address::where('customer_id', $customerId)->update(['default_billing' => 0]);
        }
        if ($request->default_shipping) {
            Customer\Address::where('customer_id', $customerId)->update(['default_shipping' => 0]);
        }

        $address = Customer\Address::where('customer_id', $customerId)->findOrNew($id);
        $address->customer_id = $customer = $customerId;
        $address->forceFill($request->only([
            'first_name',
            'last_name',
            'company',
            'address_line_1',
            'address_line_2',
            'town',
            'county',
            'postcode',
            'country_iso3',
            'phone',
            'email',
            'default_billing',
            'default_shipping',
        ]));
        $address->save();
        
        return $this->_redirect('customer.account.address', ['success' => true]);
    }

    /**
     * @param int $id
     * @return View|RedirectResponse
     */
    public function addressDelete($id)
    {
        if ($address = Customer\Address::where('customer_id', $this->_cart->getCustomerId())->find($id)) {
            $address->delete();
        }
        return $this->_redirect('customer.account.address');
    }

    /**
     * @return View
     */
    public function passwordChange()
    {
        $this->_setCustomerMeta('Change Password');
        return $this->_view('customer.account.password');
    }

    /**
     * @return RedirectResponse|View
     * @throws ValidationException
     */
    public function passwordUpdate(Hasher $hasher, Request $request)
    {
        $customer = $this->_cart->getCustomer();

        $this->validate($request, [
            'current_password' => function ($attribute, $value, $fail) use ($hasher, $customer) {
                if (!$hasher->check($value, $customer->password)) {
                    $fail('current password is incorrect');
                }
            },
            'password' => 'required|min:6|confirmed'
        ]);

        $customer->forceFill(['password' => $hasher->make($request->password)])->save();

        $this->_setCustomerMeta('Change Password');
        return $this->_view('customer.account.password', ['success' => true]);
    }

    /**
     * @return View
     */
    public function orderList()
    {
        $this->_setCustomerMeta('My Orders');
        $this->_setCustomerMenuActiveItem('My Orders');
        return $this->_view('customer.account.orders');
    }

    /**
     * @param int $id
     * @return View|RedirectResponse
     */
    public function orderView($id)
    {
        $order = Order::where('customer_id', $this->_cart->getCustomerId())->find($id);
        if (!$order) {
            return $this->_redirect('customer.account.order.list');
        }
        $this->_setCustomerMeta('Order ' . $order->order_number);
        $this->_setCustomerMenuActiveItem('My Orders');
        return $this->_view('customer.account.order', ['order' => $order]);
    }

    /**
     * @param int $id
     * @return View|RedirectResponse
     */
    public function orderReorder($id)
    {
        /** @var Order $order */
        $order = Order::where('customer_id', $this->_cart->getCustomerId())->find($id);
        if (!$order) {
            return $this->_redirect('customer.account.order.list');
        }

        $this->_cart->deleteItems();
        foreach ($order->items as $item) {
            $cartItem = null;
            if ($item->variation_id && $item->variation) {
                $cartItem = $this->_cart->addProductVariation($item->variation_id, $item->getDataArray(), $item->item_qty);
            } elseif ($item->product_id && $item->product) {
                $cartItem = $this->_cart->addProduct($item->product_id, $item->getDataArray(), $item->item_qty);
            }
            if (!$cartItem || !$cartItem->exists) {
                $this->_flashAlert('danger', __('coaster-commerce::frontend.reorder_removed_item', $this->_itemMessageVars($item)));
            } elseif ($cartItem->item_qty !== $item->item_qty) {
                $this->_flashAlert('warning', __('coaster-commerce::frontend.reorder_reduced_item', $this->_itemMessageVars($item)));
            }
        }

        if (!$this->_cart->items->count()) {
            return $this->_redirect('customer.account.order.view', ['id' => $id]);
        } else {
            return $this->_redirect('checkout.onepage');
        }
    }

    /**
     * @param Order\Item $item
     * @return array
     */
    protected function _itemMessageVars($item)
    {
        if ($item->variation_id) {
            $variation = [];
            foreach ($item->variation->variationArray() as $attribute => $value) {
                $variation[] = $attribute . ': ' . $value;
            }
            $variationText = ' (' . implode(', ', $variation) . ')';
        } else {
            $variationText = '';
        }
        return [
            'item_full_name' => $item->item_name . $variationText,
            'item_name' => $item->item_name,
            'item_request_qty' => $item->item_request_qty,
            'item_qty' => $item->item_qty,
            'cart_link' => $this->_cart->route('frontend.checkout.cart')
        ];
    }

    /**
     * @return View
     */
    public function login()
    {
        $this->_setCustomerMeta('Customer Login');

        return $this->_view('customer.login');
    }

    /**
     * @return View
     */
    public function register()
    {
        $this->_setCustomerMeta('Customer Register');

        $this->_view->share('reCaptchaAction', 'register'); // init google recaptcha (should be in template head)
        return $this->_view('customer.register');
    }

    /**
     * @param Request $request
     * @param Hasher $hasher
     * @return View|RedirectResponse|string
     * @throws \Exception
     */
    public function createUser(Request $request, Hasher $hasher)
    {
        if (!(new ReCaptcha\V3)->isValid($request['recaptcha_response'], 'register')) {
            $this->_flashAlert('danger',  __('coaster-commerce::frontend.captcha_fail'));
            return back()->withInput();
        }

        $this->validate($request, [
            'email' => 'required|email|unique:cc_customers',
            'password' => 'required|confirmed|min:6'
        ] +(new Customer\Address())->validationRules());

        $customer = (new Customer)
            ->fill([
                'email' => $request['email'],
                'password' => $hasher->make($request['password']),
                'group_id' => Setting::getValue('customer_default_group')
            ]);

        $customer->save();
        $customer->sendNewAccountEmail($request->post());

        $customer->defaultBillingAddress()->forceFill([
            'customer_id' => $customer->id,
            'first_name' => $request['first_name'],
            'last_name' => $request['last_name'],
            'town' => $request['town'],
            'company' => ucwords($request['company']),
            'address_line_1' => ucwords($request['address_line_1']),
            'address_line_2' => ucwords($request['address_line_2']),
            'country_iso3' => $request['country_iso3'],
            'county' => $request['county'],
            'postcode' => $request['postcode'],
            'phone' => $request['phone'],
            'email' => null,
            'default_billing' => true,
            'default_shipping' => true
        ])->save();

        $this->_cart->guard()->attempt($request->only(['email', 'password']));

        return $this->_redirect('customer.account');
    }

    /**
     * @param Request $request
     * @param CookieJar $cookieJar
     * @return RedirectResponse
     * @throws \Exception
     */
    public function auth(Request $request, CookieJar $cookieJar)
    {
        $credentials = $request->only(['email', 'password']);
        $otherFormData = $request->except(['password', 'password_confirmation']);

        $success = false;
        if (!$this->_cart->guard()->attempt($credentials)) {
            $redirect = ($errorPath = $request->input('error_path'))
                ? $this->_redirect($errorPath, [], 302, [], false)
                : $this->_redirect('customer.login');
            $redirect->withErrors(['email' => 'Invalid login credentials']);
        } else if ($loginPath = $request->input('login_path')) {
            $success = true;
            $redirect = $this->_redirect($loginPath, [], 302, [], false)
                ->withCookie($cookieJar->forget('customer_login_path'));
        } else {
            $success = true;
            $redirect = $this->_redirect('customer.account');
        }

        if ($success) {
            $this->_cart->getCustomer()->forceFill(['last_login' => new Carbon()])->save();
        }

        return $redirect->withInput($otherFormData);
    }

    /**
     * @return RedirectResponse
     */
    public function logout()
    {
        $this->_cart->guard()->logout();
        return $this->_redirect('customer.login');
    }

    /**
     * @return View
     */
    public function passwordReset()
    {
        $this->_setCustomerMeta('Password Reset');
        return $this->_view('customer.reset.form');
    }

    /**
     * @param Request $request
     * @param PasswordBrokerFactory $passwordBrokerFactory
     * @return View
     */
    public function passwordResetEmail(Request $request, PasswordBrokerFactory $passwordBrokerFactory)
    {
        $this->validate($request, [
            'email' => 'required|email'
        ]);

        $credentials = $request->only('email');
        $passwordBrokerFactory->broker('cc-customer')->sendResetLink($credentials);


        $this->_setCustomerMeta('Password Reset');
        return $this->_view('customer.reset.sent', $credentials);
    }

    /**
     * @param Request $request
     * @param string $token
     * @return View
     */
    public function passwordResetUpdate(Request $request, $token)
    {
        $this->_setCustomerMeta('Password Reset');
        return $this->_view('customer.reset.update', [
            'token' => $token,
            'email' => $request->input('email')
        ]);
    }

    /**
     * @param Request $request
     * @param PasswordBrokerFactory $passwordBrokerFactory
     * @param Hasher $hasher
     * @param string $token
     * @return RedirectResponse
     */
    public function passwordResetSave(Request $request, PasswordBrokerFactory $passwordBrokerFactory, Hasher $hasher, $token)
    {
        $credentials = $request->only(['email', 'password', 'password_confirmation']) + ['token' => $token];
        $result = $passwordBrokerFactory->broker('cc-customer')->reset($credentials, function ($customer, $password) use($hasher) {
            /** @var Customer $customer */
            $customer->forceFill(['password' => $hasher->make($password)])->save();
            $this->_cart->guard()->login($customer);
        });

        if ($result == Password::PASSWORD_RESET) {
            return $this->_redirect('customer.account');
        } else {
            return redirect()->back()
                ->withInput($request->only('email'))
                ->withErrors(['password' => trans($result)]);
        }
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     * @throws ValidationException
     */
    public function stockNotify(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email',
            'product_id' => 'required|exists:cc_products,id'
        ]);

        $notification = Customer\StockNotify::firstOrCreate($request->only(['email', 'product_id']));
        if ($notification->sent) {
            $notification->sent = 0;
            $notification->save();
        }

        $this->_flashAlert('success',  __('coaster-commerce::frontend.stock_notify'));
        return redirect()->back();
    }

}

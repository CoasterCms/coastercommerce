<?php
namespace CoasterCommerce\Core\Controllers\Frontend;

use CoasterCommerce\Core\Currency\Format;
use CoasterCommerce\Core\Events\ValidateOrderAddress;
use CoasterCommerce\Core\Model\Category;
use CoasterCommerce\Core\Model\Customer;
use CoasterCommerce\Core\Model\Order;
use CoasterCommerce\Core\Model\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

class OrderController extends AbstractController
{
    use ValidatesRequests;

    /**
     * Messages display if stock limits for any product in the cart are exceeded
     * @param Order $order
     * @param bool $validCoupon
     * @param bool $showSuccess
     * @return array
     */
    protected function _generateCartUpdateMessages($order, $validCoupon, $showSuccess = false)
    {
        $messages = [];
        foreach ($this->_cart->stockLimitedItems() as $stockLimitedItem) {
            $messagesVars = $this->_itemMessageVars($stockLimitedItem);
            if ($stockLimitedItem->item_qty == 0) {
                if ($stockLimitedItem->getProductStock() === 0) {
                    $messages['danger'][] = __('coaster-commerce::frontend.cart_stock_none', $messagesVars);
                } else {
                    $messages['danger'][] = __('coaster-commerce::frontend.cart_stock_none_variants', $messagesVars);
                }
            } else {
                $messages['warning'][] = __('coaster-commerce::frontend.cart_stock_limited', $messagesVars);
            }
        }
        if (!$validCoupon && $order->order_coupon) {
            $messages['danger'][] = __('coaster-commerce::frontend.cart_invalid_coupon');
        }
        if (!$messages && $showSuccess) {
            $messages['success'][] = __('coaster-commerce::frontend.cart_update_success');
        }
        return $messages;
    }

    /**
     * Generate message displayed after attempting to add item to cart
     * @param Order\Item $item
     * @return array
     */
    protected function _generateItemAddMessage($item)
    {
        $messagesVars = $this->_itemMessageVars($item);
        $messages = [];
        if ($item->exists) {
            if ($item->item_qty == $item->item_request_qty) {
                $messages['success'][] = __('coaster-commerce::frontend.product_add_success', $messagesVars);
            } else {
                $messages['warning'][] = __('coaster-commerce::frontend.product_add_stock_limited', $messagesVars);
            }
        } else {
            if ($item->getProductStock() === 0) {
                $messages['danger'][] = __('coaster-commerce::frontend.product_add_stock_none', $messagesVars);
            } else {
                $messages['danger'][] = __('coaster-commerce::frontend.product_add_stock_none_variants', $messagesVars);
            }
        }
        return $messages;
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
     * Can add product or variation
     * @param Request $request
     * @return RedirectResponse|JsonResponse
     */
    public function cartAdd(Request $request)
    {
        if ($request->post('variation_id')) {
            return $this->cartAddVariation($request);
        }
        try {
            $item = $this->_cart->addProduct(
                $request->post('product_id'),
                $request->post('option', []),
                max((int) $request->post('qty', 1), 1)
            );
            $messages = $this->_generateItemAddMessage($item);
        } catch (\Exception $e) {
            // can fail on products that have variations (variation must be selected to add to cart)
            $messages = [];
            $messages['danger'][] = $e->getMessage();
            $product = Product::find($request->post('product_id'));
            $category = Category::find($request->post('category_id')); // keeps breadcrumb trail if category_id provided
            return redirect()->to($product->getUrl($category))->with(['coaster-commerce.frontend-messages' => $messages])->withInput();
        }
        return $request->ajax() ?
            response()->json($messages) :
            $this->_redirect('checkout.cart')->with(['coaster-commerce.frontend-messages' => $messages]);
    }

    /**
     * @param Request $request
     * @return RedirectResponse|JsonResponse
     */
    public function cartAddVariation(Request $request)
    {
        $item = $this->_cart->addProductVariation(
            $request->post('variation_id'),
            $request->post('option', []),
            max((int) $request->post('qty', 1), 1)
        );
        $messages = $this->_generateItemAddMessage($item);
        return $request->ajax() ?
            response()->json(['coaster-commerce.frontend-messages' => $messages]) :
            $this->_redirect('checkout.cart')->with(['coaster-commerce.frontend-messages' => $messages]);
    }

    /**
     * @param int $id
     * @return RedirectResponse
     */
    public function cartRemove($id)
    {
        $this->_cart->deleteItem($id);
        return $this->_redirect('checkout.cart');
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function cartUpdate(Request $request)
    {
        $redirectToCheckout = stripos($request->post('action'), 'checkout') !== false;
        $itemQuantities = $request->post('qty', []);
        foreach ($itemQuantities as $itemId => $quantity) {
            $this->_cart->updateItemQty($itemId, $quantity, false); // last param false, to update order at end (less db updates)
        }
        $itemOptions = $request->post('option', []);
        foreach ($itemOptions as $itemId => $options) {
            $this->_cart->updateItemOptions($itemId, $options);
        }
        $this->_cart->order_coupon = $request->post('order_coupon');
        // save order with order stock / coupon checks
        $messages = [];
        $this->_cart->recalculateItems(function (Order $order, $validCoupon) use(&$messages, $redirectToCheckout) {
            $messages = $this->_generateCartUpdateMessages($order, $validCoupon, !$redirectToCheckout);
        }, false);
        return $redirectToCheckout
            ? $this->_redirect('checkout.onepage')->with(['coaster-commerce.frontend-messages' => $messages])
            : $this->_redirect('checkout.cart')->with(['coaster-commerce.frontend-messages' => $messages]);
    }

    /**
     * @return RedirectResponse
     */
    public function cartClear()
    {
        $this->_cart->deleteItems();
        $messages = ['success' => [__('coaster-commerce::frontend.cart_clear_success')]];
        return $this->_redirect('checkout.cart')->with(['coaster-commerce.frontend-messages' => $messages]);
    }

    /**
     * @return View
     */
    public function cart()
    {
        $this->_cart->recalculateItems(function (Order $order, $validCoupon) {
            $messages = $this->_generateCartUpdateMessages($order, $validCoupon);
            $this->_addAlerts($messages);
        });
        $this->_setPageMeta(__('coaster-commerce::frontend.cart_name'));
        return $this->_view('checkout.cart');
    }

    /**
     * @return JsonResponse
     */
    public function cartJson()
    {
        return response()->json([
            'itemCount' => $this->_cart->getItemCount(),
            'itemQty' => $this->_cart->getItemCount() ? $this->_cart->items->sum('item_qty') : 0
        ]);
    }

    /**
     * @return View|RedirectResponse
     */
    public function checkout()
    {
        $messages = [];
        $this->_cart->recalculateItems(function (Order $order, $validCoupon) use(&$messages) {
            $messages = $this->_generateCartUpdateMessages($order, $validCoupon);
            $this->_addAlerts($messages);
        });
        if (!$this->_cart->getItemCount()) {
            return $this->_redirect('checkout.cart')->with(['coaster-commerce.frontend-messages' => $messages]);
        }
        $this->_setPageMeta(__('coaster-commerce::frontend.checkout_name'));
        return $this->_view('checkout.onepage', [
            'billingAddress' => new Order\Address(),
            'shippingAddress' => new Order\Address()
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function checkoutCheckEmail(Request $request)
    {
        $email = $request->post('email');
        return response()->json(['result' => $email ? Customer::where('email', $email)->count() : 0]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function checkoutSaveEmail(Request $request)
    {
        if ($customer = $this->_cart->getCustomer()) {
            $this->_cart->email = $customer->email;
        } else {
            $v = validator(['email' => $request->post('email')], ['email' => 'email']);
            if (!$v->fails()) {
                $this->_cart->email = $request->post('email');
            }
        }
        $this->_cart->save();
        return response()->json('saved');
    }

    /**
     * @return array
     */
    protected function _checkoutResponseData()
    {
        $responseData = [];
        // return available shipping methods
        $responseData['shipping_methods'] = [];
        foreach ($this->_cart->getAvailableShippingMethods() as $shippingMethod) {
            $responseData['shipping_methods'][] = [
                'id' => $shippingMethod->code,
                'type' => $shippingMethod->type(),
                'name' => $shippingMethod->name(),
                'desc' => $shippingMethod->description() ?: '',
                'rate' => $shippingMethod->rate(),
                'rate_formatted' => (new Format($shippingMethod->rate()))->__toString()
            ];
        }
        // return available payment methods
        $responseData['payment_methods'] = [];
        foreach ($this->_cart->getAvailablePaymentMethods() as $paymentMethod) {
            $responseData['payment_methods'][] = [
                'id' => $paymentMethod->code,
                'type' => $paymentMethod->type(),
                'name' => $paymentMethod->name(),
                'desc' => $paymentMethod->description() ?: ''
            ] ;
        }
        return $responseData;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function checkoutSaveAddress(Request $request)
    {
        $customer = $this->_cart->getCustomer();
        $shipping = $request->post('shipping');
        $billing = $request->post('billing');

        // generate general validation rules (email)
        $validationRules = [];
        if (!$customer) { // main order email field for guests
            $validationRules['email'] = 'required|email';
        }

        // default shipping/billing address fields for use below
        $fillFields = array_fill_keys([
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
        ], null);

        // generate validation rules for shipping address & populate model
        if (!$this->_cart->isVirtual()) {
            $shippingAddress = ($customer && $shipping['id']) ? $customer->addresses->where('id', $shipping['id'])->first() : null;
            if (!$shippingAddress) {
                foreach ((new Customer\Address())->validationRules() as $field => $rule) {
                    $validationRules['shipping.' . $field] = $rule;
                }
                $shippingAddress = (new Customer\Address())
                    ->forceFill(array_intersect_key($shipping, $fillFields));
            }
        }

        // generate validation rules for billing address & populate model
        if (!empty($billing['same']) && isset($shippingAddress)) {
            $billingAddress = $shippingAddress;
        } else {
            $billingAddress = ($customer && $billing['id']) ? $customer->addresses->where('id', $billing['id'])->first() : null;
            if (!$billingAddress) {
                foreach ((new Customer\Address())->validationRules() as $field => $rule) {
                    $validationRules['billing.' . $field] = $rule;
                }
                $billingAddress = (new Customer\Address())
                    ->forceFill(array_intersect_key($billing, $fillFields + ['email' => null]));
            }
        }

        // validate all and save email / address data to order
        event(new ValidateOrderAddress($request, $validationRules, $billingAddress, $shippingAddress ?? null));
        $this->_cart->addresses()->whereIn('type', ['shipping', 'billing'])->delete();
        if (isset($shippingAddress)) {
            $this->_cart->addresses()->save($shippingAddress->convertToOrderAddress('shipping'));
        }
        $this->_cart->addresses()->save($billingAddress->convertToOrderAddress('billing'));
        $this->_cart->email = $customer ? $customer->email : $request->post('email');
        $this->_cart->save(['recalculate_items' => true]); // recalculate tax zones & thus vat amounts (country may have changes)

        return response()->json($this->_checkoutResponseData());
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function checkoutSaveShipping(Request $request)
    {
        if (!$this->_cart->updateShippingMethod($request->post('shipping_method'))) {
            throw new \Exception('Invalid shipping method, please refresh page');
        }
        $this->_cart->save();

        return response()->json($this->_checkoutResponseData());
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function checkoutSavePayment(Request $request)
    {
        if (!$this->_cart->updatePaymentMethod($request->post('payment_method'))) {
            throw new \Exception('Invalid payment method, please refresh page');
        }
        $this->_cart->save();

        return response()->json($this->_checkoutResponseData());
    }

    /**
     * @param Request $request
     * @return RedirectResponse|View
     */
    public function checkoutPaymentGateway(Request $request)
    {
        if ($comment = $request->post('comment')) {
            $note = $this->_cart->notes()->first() ?: new Order\Note();
            $note->customer_notified = 1;
            $note->note = $comment;
            $note->order_id = $this->_cart->id;
            $note->save();
        }
        if (!($paymentMethod = $this->_cart->updatePaymentMethod($request->post('payment_method')))) {
            $messages = ['danger' => ['Invalid payment method']];
            return $this->_redirect('checkout.onepage')->with(['coaster-commerce.frontend-messages' => $messages]);
        }
        $this->_cart->save(); // save updated payment method
        if (!$this->_cart->isVirtual() && !$this->_cart->getShippingMethod()) {
            $messages = ['danger' => ['Invalid shipping method']];
            return $this->_redirect('checkout.onepage')->with(['coaster-commerce.frontend-messages' => $messages]);
        }
        return $paymentMethod->paymentGateway($request);
    }

    /**
     * @param Request $request
     * @param string $orderKey
     * @return RedirectResponse|View|string
     */
    public function checkoutCallbackSuccess(Request $request, $orderKey)
    {
        return $this->_checkoutPaymentCallback($request, 'success', $orderKey);
    }

    /**
     * @param Request $request
     * @param string $orderKey
     * @return RedirectResponse|View|string
     */
    public function checkoutCallbackFailure(Request $request, $orderKey)
    {
        return $this->_checkoutPaymentCallback($request, 'failure', $orderKey);
    }

    /**
     * @param Request $request
     * @param string $action
     * @param string $orderKey
     * @return RedirectResponse|View|string
     */
    public function _checkoutPaymentCallback(Request $request, $action, $orderKey)
    {
        if ($order = Order::loadByKey($orderKey)) {
            $paymentMethod = $order->getPaymentMethod();
            return $action == 'success' ? $paymentMethod->callbackSuccess($request) : $paymentMethod->callbackFailure($request);
        }
        return 'Invalid callback';
    }

    /**
     * @param string $orderKey
     * @return View|string
     */
    public function checkoutComplete($orderKey)
    {
        if ($order = Order::loadByKey($orderKey)) {
            if ($order->order_placed->diffInMinutes('now') < 60) {
                $this->_setPageMeta('Order Received');
                return $this->_view('checkout.complete', ['order' => $order]);
            }
        }
        return $this->cart();
    }

    /**
     * @return View
     */
    public function checkoutSummary()
    {
        return $this->_view('checkout.summary');
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function quickOrder(Request $request)
    {
        $this->_cart->deleteItems();

        $notFoundErrors = [];
        foreach ($request['product'] as $product) {
            if ($product['part_number']) {
                if ($_product = DB::table('cc_products')->select('id')->where('sku', $product['part_number'])->first()) {
                    $this->_cart->addProduct($_product->id, [], $product['qty']);
                } else {
                    $notFoundErrors[] = 'Product "' . $product['part_number'] . '" not found';
                }
            }
        }

        $messages = $this->_generateCartUpdateMessages($this->_cart->getOrder(), false, empty($notFoundErrors));
        foreach ($notFoundErrors as $notFoundError) {
            $messages['danger'][] = $notFoundError;
        }

        $this->_cart->save();

        if ($this->_cart->items->count()) {
            return $this->_redirect('checkout.onepage')->with(['coaster-commerce.frontend-messages' => $messages]);
        } else {
            return redirect()->back()->with(['coaster-commerce.frontend-messages' => $messages]);
        }
    }

}
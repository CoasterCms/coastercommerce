<?php namespace CoasterCommerce\Core\Listeners;

use CoasterCommerce\Core\Events\AdminPromotionSave as AdminPromotionSaveEvent;
use CoasterCommerce\Core\Model\Promotion\Coupon;
use Illuminate\Validation\Factory;
use Illuminate\Validation\ValidationException;

class AdminPromotionSave
{

    /**
     * @var Factory
     */
    protected $_validator;

    /**
     * AdminPromotionSave constructor.
     * @param Factory $validation
     */
    public function __construct(Factory $validation)
    {
        $this->_validator = $validation;
    }

    /**
     * @param AdminPromotionSaveEvent $event
     * @throws ValidationException
     */
    public function handle(AdminPromotionSaveEvent $event)
    {
        // save customer groups
        $event->promotion->customerGroups()->sync(
            array_key_exists('group_ids', $event->inputData) ? $event->inputData['group_ids'] : []
        );
        // save customers
        $event->promotion->customers()->sync(
            array_key_exists('customer_ids', $event->inputData) ? $event->inputData['customer_ids'] : []
        );
        // save categories
        $event->promotion->categories()->sync(
            array_key_exists('category_ids', $event->inputData) ? $event->inputData['category_ids'] : []
        );
        // save products
        $event->promotion->products()->sync(
            array_key_exists('product_ids', $event->inputData) ? $event->inputData['product_ids'] : []
        );
        // save coupons
        $currentCoupons = $event->promotion->coupons->keyBy('code');
        $postCouponData = request()->post('coupon', []);
        $deleteIds = request()->post('coupon_deleted_ids', []);
        foreach ($postCouponData as $postCoupon) {
            if (array_key_exists('code', $postCoupon) && $postCoupon['code']) {
                $coupon = $currentCoupons->offsetExists($postCoupon['code']) ? $currentCoupons->offsetGet($postCoupon['code']) : new Coupon();
                $coupon->forceFill([
                    'promotion_id' => $event->promotion->id,
                    'code' => $postCoupon['code'],
                    'uses_left' => is_null($postCoupon['uses_left']) ? $postCoupon['uses_left'] : (int) $postCoupon['uses_left']
                ])->save();
                $currentCoupons->offsetSet($postCoupon['code'], $coupon);
            }

        }
        $event->promotion->coupons()->whereIn('id', $deleteIds)->delete();

    }

}


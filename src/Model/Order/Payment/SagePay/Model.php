<?php

namespace CoasterCommerce\Core\Model\Order\Payment\SagePay;

use CoasterCommerce\Core\Model\Order;
use Illuminate\Database\Eloquent\Model as Eloquent;

class Model extends Eloquent
{

    /**
     * @var string
     */
    protected $table = 'cc_order_payment_sagepay';

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    /**
     * @param string $txCode
     * @return static
     */
    public static function findWithTxCode($txCode)
    {
        return (new static)->where('tx_code', $txCode)->first();
    }

    /**
     * @param string $txCode
     * @param int $orderId
     */
    public static function saveTxCode($txCode, $orderId)
    {
        if (!static::findWithTxCode($txCode)) {
            (new static)->forceFill([
                'tx_code' => $txCode,
                'order_id' => $orderId
            ])->save();
        }
    }

    /**
     * @param array $paymentData
     * @return static
     */
    public function setPaymentData($paymentData)
    {
        $this->payment_data = json_encode($paymentData);
        return $this;
    }

    /**
     * @return array
     */
    public function getPaymentData()
    {
        return $this->payment_data ? json_decode($this->payment_data, true) : [];
    }

}

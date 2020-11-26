<?php

namespace CoasterCommerce\Core\Model;

use CoasterCommerce\Core\Model\Currency as CurrencyModel;
use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{

    public $table = 'cc_currencies';

    /**
     * @var array
     */
    public static $loadedCurrencies = [];

    /**
     * @param $currencyId
     * @return CurrencyModel
     */
    public static function getModel($currencyId = null)
    {
        $currencyId = $currencyId ?: session('coaster-commerce.currency-id');
        $currencyId = (int) ($currencyId ?: Setting::getValue('default_currency_id'));
        if (!array_key_exists($currencyId, static::$loadedCurrencies)) {
            if ($currencyId) {
                static::$loadedCurrencies[$currencyId] = static::find($currencyId) ?: new static;
            } else {
                static::$loadedCurrencies[$currencyId] = static::all()->first() ?: new static;
            }
        }
        return static::$loadedCurrencies[$currencyId];
    }

}

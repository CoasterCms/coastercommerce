<?php
namespace CoasterCommerce\Core\Currency;

use CoasterCommerce\Core\Model\Currency as CurrencyModel;

class Format
{

    /**
     * @var float
     */
    protected $_value;

    /**
     * @var CurrencyModel
     */
    protected $_currency;

    /**
     * @var bool
     */
    protected $_zero;

    /**
     * Format constructor.
     * @param float $value
     * @param int $currencyId
     */
    public function __construct($value, $currencyId = null)
    {
        $this->_value = (float) $value;
        $this->_currency = CurrencyModel::getModel($currencyId);
    }

    /**
     * @param bool $showZero
     * @return $this
     */
    public function showZero($showZero = true)
    {
        $this->_zero = $showZero;
        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        if (!$this->_value && !$this->_zero) {
            return 'Free';
        }
        return $this->_currency->prefix . number_format($this->_value, 2) . $this->_currency->suffix;
    }

}

<?php

namespace CoasterCommerce\Core\Model\Order\Shipping;

use CoasterCommerce\Core\Model\Order;
use CoasterCommerce\Core\Model\Order\Shipping;
use Illuminate\Support\Str;

abstract class AbstractShipping
{

    /**
     * @var Shipping
     */
    protected $_model;

    /**
     * @var Order
     */
    protected $_order;

    /**
     * @var array
     */
    protected $_alerts = [];

    /**
     * AbstractShipping constructor.
     * @param Shipping $methodModel
     * @param Order $order
     */
    public function __construct($methodModel, $order = null)
    {
        $this->_model = $methodModel;
        $this->_order = $order ?: new Order;
    }

    /**
     * @return string
     */
    public function type()
    {
        return ucwords(Str::snake(substr(get_class($this), strrpos(get_class($this), '\\') + 1), ' '));
    }

    /**
     * Frontend name for method
     * @return string
     */
    public function name()
    {
        return $this->_model->name;
    }

    /**
     * Frontend description for method on checkout
     * @return string
     */
    public function description()
    {
        return $this->_model->description;
    }

    /**
     * @return bool
     */
    public function isAvailable()
    {
        return true;
    }

    /**
     * @param string $name
     * @return string
     */
    public function getCustomField($name)
    {
        $config = $this->_model->custom_config ? json_decode($this->_model->custom_config, true) : [];
        return array_key_exists($name, $config) ? $config[$name] : null;
    }

    /**
     * @param string $name
     * @param string $value
     */
    public function setCustomField($name, $value)
    {
        $config = $this->_model->custom_config ? json_decode($this->_model->custom_config, true) : [];
        if (is_null($value)) {
            unset($config[$name]);
        } else {
            $config[$name] = $value;
        }
        $this->fillCustomFields($config);
    }

    /**
     * @param array $config
     */
    public function fillCustomFields($config)
    {
        $this->_model->custom_config = $config ? json_encode($config) : null;
    }

    /**
     * Renders custom settings in the admin
     * @return string
     */
    public function renderCustomFields()
    {
        return '';
    }

    /**
     * @return Shipping
     */
    public function getModel()
    {
        return $this->_model;
    }

    /**
     * Used to return msgs when saving method
     * @return array
     */
    public function getAlerts()
    {
        return $this->_alerts;
    }

    /**
     * @param $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->_model->$name;
    }

    /**
     * @return float
     */
    abstract public function rate();

}

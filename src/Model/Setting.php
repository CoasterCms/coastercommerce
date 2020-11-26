<?php

namespace CoasterCommerce\Core\Model;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{

    /**
     * @var string
     */
    protected $table = 'cc_settings';

    /**
     * @var array
     */
    protected static $settings;

    /**
     * @param string $setting
     * @return Setting
     */
    public function getBySetting($setting)
    {
        return $this->where('setting', $setting)->first();
    }

    /**
     * @param string $setting
     * @return mixed
     */
    public static function getValue($setting)
    {
        if (is_null(static::$settings)) {
            static::$settings = static::pluck('value', 'setting')->toArray();
        }
        return array_key_exists($setting, static::$settings) ? static::$settings[$setting] : null;
    }

    /**
     * @param string $setting
     * @param mixed $value
     * @return $this
     */
    public function setValue($setting, $value)
    {
        /** @var Setting $settingModel */
        $settingModel = $this->getBySetting($setting) ?: $this;
        $settingModel->setting = $setting;
        $settingModel->saveValue($value);
        return $settingModel;
    }

    /**
     * @param mixed $value
     */
    public function saveValue($value)
    {
        $this->value = (string) $value;
        $this->save();
    }

    /**
     * Return next order number and increment next_order_number in database
     * @return int
     */
    public static function nextOrderNumber()
    {
        $setting = (new static)->getBySetting('next_order_number');
        $orderNumber = (int) $setting->value;
        $setting->saveValue($orderNumber + 1);
        return $orderNumber;
    }

}

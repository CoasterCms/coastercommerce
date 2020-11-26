<?php

namespace CoasterCommerce\Core\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class EmailSetting extends Model
{

    public $table = 'cc_email_settings';

    /**
     * @var Collection|static[]
     */
    protected static $_models;

    /**
     * @param string $mailable
     * @return static
     */
    public static function getSettings($mailable)
    {
        if (is_null(static::$_models)) {
            static::$_models = static::all()->keyBy('mailable');
        }
        return static::$_models->offsetExists($mailable) ? static::$_models->offsetGet($mailable) : null;
    }

}

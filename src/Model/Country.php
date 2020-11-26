<?php

namespace CoasterCommerce\Core\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use League\ISO3166\ISO3166;

class Country extends Model
{

    public $table = 'cc_countries';

    /**
     * @var Collection
     */
    public static $iso3Names;

    /**
     * @param string $iso3
     * @return string
     */
    public static function name($iso3)
    {
        static::_loadNames();
        if (!static::$iso3Names->offsetExists($iso3) || !static::$iso3Names->offsetGet($iso3)) {
            try {
                // load default name from library
                return (new ISO3166)->alpha3($iso3)['name'];
            } catch (\Exception $e) {
                return null;
            }
        }
        // otherwise use database name
        return static::$iso3Names->offsetGet($iso3);
    }

    /**
     * @return Collection
     */
    public static function names()
    {
        static::_loadNames();
        $names = collect();
        foreach (static::$iso3Names as $iso3 => $name) {
            if (!$name) {
                try {
                    $name = (new ISO3166)->alpha3($iso3)['name'];
                } catch (\Exception $e) {}
            }
            if ($name) {
                $names->put($iso3, $name);
            }
        }
        return $names->sort();
    }

    /**
     *
     */
    protected static function _loadNames()
    {
        if (is_null(static::$iso3Names)) {
            static::$iso3Names = (new static)->pluck('name', 'iso3');
        }
    }

}

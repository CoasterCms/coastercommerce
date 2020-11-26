<?php

namespace CoasterCommerce\Core\Model\Order;

use CoasterCommerce\Core\Model\Order;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class Status extends Model
{

    /**
     * @var string
     */
    protected $table = 'cc_order_status';

    /**
     * @var static[]
     */
    protected static $_allStatuses;

    /**
     * @param $state
     * @return static
     */
    public static function getDefaultStatus($state)
    {
        return static::getAllStatuses()->where('state', $state)->where('state_default', 1)->first();
    }

    /**
     * @param $status
     * @return static
     */
    public static function getStatus($status)
    {
        return static::getAllStatuses()->where('code', $status)->first();
    }

    /**
     * @return static[]|Collection
     */
    public static function getAllStatuses()
    {
        if (is_null(static::$_allStatuses)) {
            static::$_allStatuses = static::all();
        }
        return static::$_allStatuses;
    }

    /**
     * Only order with these statuses are visible in the admin order list
     * @return array
     */
    public static function visibleStatuses()
    {
        return static::getAllStatuses()->where('visible', 1)->pluck('code')->toArray();
    }

    /**
     * Non quote state statuses
     * @return array
     */
    public static function submittedStatuses()
    {
        return static::getAllStatuses()->where('state', '!=', Order::STATUS_QUOTE)->pluck('code')->toArray();
    }

    /**
     * Quote state statuses
     * @return array
     */
    public static function quoteStatuses()
    {
        return static::getAllStatuses()->where('state', Order::STATUS_QUOTE)->pluck('code')->toArray();
    }

    /**
     * Complete state statuses
     * @return array
     */
    public static function completeStatuses()
    {
        return static::getAllStatuses()->where('state', Order::STATUS_COMPLETE)->pluck('code')->toArray();
    }

}

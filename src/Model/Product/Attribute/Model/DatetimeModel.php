<?php

namespace CoasterCommerce\Core\Model\Product\Attribute\Model;

use Carbon\Carbon;
use Illuminate\Support\Facades\Date;

class DatetimeModel extends AbstractModel
{

    /**
     * @var string
     */
    protected $_dateFormat;

    /**
     * Get format from database connection and store result
     * @return mixed
     */
    public function getDateFormat()
    {
        if (is_null($this->_dateFormat)) {
            $this->_dateFormat = app('db.connection')->getQueryGrammar()->getDateFormat();
        }
        return $this->_dateFormat;
    }

    /**
     * @param mixed $value
     * @return Carbon
     */
    public function databaseToCollection($value)
    {
        return $value ? Date::createFromFormat($this->getDateFormat(), $value) : null;
    }

}

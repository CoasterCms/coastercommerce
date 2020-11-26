<?php

namespace CoasterCommerce\Core\Model\Product\Attribute\Frontend;

use CoasterCommerce\Core\Model\Product\Attribute;
use Illuminate\Database\Query\Builder;
use DateTime;

class DateFrontend extends AbstractFrontend
{

    /**
     * @param Attribute $attribute
     * @param string $value
     * @param int $id
     * @return string
     */
    public function dataTableCellValue($attribute, $value, $id)
    {
        return is_a($value,DateTime::class) ? $value->format('Y-m-d H:i:s') : '';
    }

    /**
     * @param Attribute $attribute
     * @param string $value
     * @return string
     */
    public function submissionRules($attribute, $value)
    {
        $additionalRule = 'date_format:Y-m-d H:i:s';
        return $value ? $value . '|' . $additionalRule : $additionalRule;
    }

    /**
     * @param Attribute $attribute
     * @param mixed $filterValue
     * @param Builder $query
     * @return Builder
     */
    public function filterQuery($attribute, $filterValue, $query)
    {
        if (is_array($filterValue)) {
            if (!is_null($filterValue['from']) && !is_null($filterValue['to'])) {
                return $query->whereBetween($attribute->code, [$filterValue['from'], $filterValue['to']]);

            } elseif (!is_null($filterValue['from'])) {
                return $query->where($attribute->code, '>', $filterValue['from']);

            } elseif (!is_null($filterValue['to'])) {
                return $query->where($attribute->code, '<', $filterValue['to']);
            }
        }
        return $query;
    }

    /**
     * @return string
     */
    public function defaultModel()
    {
        return 'datetime';
    }

}

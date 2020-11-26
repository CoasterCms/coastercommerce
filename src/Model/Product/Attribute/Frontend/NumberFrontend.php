<?php

namespace CoasterCommerce\Core\Model\Product\Attribute\Frontend;

use CoasterCommerce\Core\Model\Product\Attribute;
use Illuminate\Database\Query\Builder;

class NumberFrontend extends AbstractFrontend
{

    protected $_inputView = 'text';

    /**
     * @param Attribute $attribute
     * @param string $value
     * @return string
     */
    public function submissionRules($attribute, $value)
    {
        $rules = explode('|', $value);
        if (strpos($value, 'integer') === false) {
            $rules[] = 'numeric';
        }
        if (strpos($value, 'required') === false) {
            $rules[] = 'nullable';
        }
        return implode('|', $rules);
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

}

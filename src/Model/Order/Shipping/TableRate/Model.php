<?php

namespace CoasterCommerce\Core\Model\Order\Shipping\TableRate;

use Illuminate\Database\Eloquent\Model as Eloquent;

class Model extends Eloquent
{

    /**
     * @var string
     */
    protected $table = 'cc_shipping_rates';

    /**
     * @param string $method
     * @return mixed
     */
    public static function getRates($method)
    {
        return (new static)->where('method', $method)
            ->orderBy('country_iso3', 'asc')
            ->orderBy('postcode', 'asc')
            ->orderBy('shipping_rate', 'asc')
            ->select(array_keys(static::getRateHeaders()))
            ->get()->toArray();
    }

    /**
     * @return array
     */
    public static function getRateHeaders()
    {
        return [
            'country_iso3' => 'Country Code (ISO3)',
            'postcode' => 'Postcode',
            'condition_filter' => 'Condition Filter',
            'condition_min' => 'Condition Min Value',
            'condition_max' => 'Condition Max Value',
            'shipping_rate' => 'Shipping Cost'
        ];
    }

    /**
     * @param string $method
     * @param string $condition
     * @param mixed $conditionValue
     * @param string $country
     * @param string $postcode
     * @return float
     */
    public static function getRateForDestination($method, $condition, $conditionValue, $country, $postcode)
    {
        $country = $country ?: '*';
        $postcode = strtoupper(preg_replace('/[\W_]/', '', $postcode)) ?: '*';

        $query = (new static)->where('method', $method);

        if ($condition) {
            $query
                ->where(function ($q) use($condition) {
                    $q->where('condition_filter', $condition)->orWhereNull('condition_filter');
                })->where(function ($q) use($conditionValue) {
                    $q->where('condition_min', '<=', $conditionValue)->orWhereNull('condition_min');
                })->where(function ($q) use($conditionValue) {
                    $q->where('condition_max', '>=', $conditionValue)->orWhereNull('condition_max');
                });
        }

        // do country check via sql & order by specificness
        $shippingTableRates = $query
            ->where(function ($countryQ) use($country) {
                $countryQ
                    ->where('country_iso3', $country)
                    ->orWhere('country_iso3', '*')
                    ->orWhereNull('country_iso3');
            })
            ->orderBy('country_iso3', 'desc') // get matching country first otherwise *
            ->orderBy('postcode', 'desc') // gets most specific postcode rule match otherwise *
            ->orderBy('condition_min', 'desc') // finally get highest condition value
            ->get();

        // do postcode check via php (should be already ordered by specificness)
        $matchingRate = null;
        foreach ($shippingTableRates as $shippingTableRate) {
            $postcodeRule = strtoupper(preg_replace('/\s/', '', $shippingTableRate->postcode)) ?: '*';
            // exact, wildcard or blank rule match
            if (in_array($postcodeRule, [$postcode, '*', null]) ||
                preg_match(str_replace('*', '(.*)', '#^'. $postcodeRule . '$#'), $postcode)
            ) {
                $matchingRate = $shippingTableRate;
            }
            // postcode range match
            if (preg_match('#^([A-Z]*)(\d+)-(\d+)\**$#', $postcodeRule, $formatMatches)) {
                if (preg_match('#^'.$formatMatches[1].'(\d{0,2})([A-Z]*).*$#', $postcode, $matches)) {
                    $district = (int) ($matches[2] ? substr($matches[1], 0, -1) : $matches[1]);
                    if ($district <= (int) $formatMatches[3] && $district >= (int) $formatMatches[2]) {
                        $matchingRate = $shippingTableRate;
                    }
                }
            }
            if ($matchingRate) {
                break;
            }
        }

        return $matchingRate ? $matchingRate->shipping_rate : null;
    }

}

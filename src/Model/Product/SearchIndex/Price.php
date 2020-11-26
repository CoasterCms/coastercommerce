<?php

namespace CoasterCommerce\Core\Model\Product\SearchIndex;

use CoasterCommerce\Core\Model\Customer;
use CoasterCommerce\Core\Model\Product;
use CoasterCommerce\Core\Model\Promotion;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Price extends Model
{

    public $table = 'cc_product_search_price_index';

    public $dates = ['expires'];

    public $timestamps = false;

    protected $_customerGroupIds;

    protected $_basePrices = [];

    /**
     * @param Product $product
     */
    public function reindexProduct($product)
    {
        $searchIndexData = collect();
        Promotion::setIndexFlag(true);
        $this->_newRows($searchIndexData, $product);
        Promotion::setIndexFlag(false);
        (new static)->where('product_id', $product->id)->delete();
        (new static)->insert($searchIndexData->toArray());
    }

    /**
     * @param Product[] $products
     * @param bool $truncate
     */
    public function reindexAll($products, $truncate = true)
    {
        $searchIndexData = collect();
        Promotion::setIndexFlag(true);
        foreach ($products as $product) {
            $this->_newRows($searchIndexData, $product);
        }
        Promotion::setIndexFlag(false);
        if ($truncate) {
            static::truncate();
        } else {
            static::whereIn('product_id', $products->pluck('id')->toArray())->delete();
        }
        $searchIndexData->chunk(100)->each(function ($searchIndexChunk) {
            static::insert($searchIndexChunk->toArray());
        });
    }

    /**
     * @param Collection $searchIndexData
     * @param Product $product
     */
    protected function _newRows($searchIndexData, $product)
    {
        $promotions = Promotion::getActivePromotions($product, null, null);

        $globalPromotions = [];
        $customerPromotions = [];
        $groupPromotions = [];
        foreach ($promotions as $promotion) {
            $customerSpecific = false;
            if ($promotion->customersPivot->count()) {
                foreach ($promotion->customersPivot as $customerPivot) {
                    $customerPromotions[$customerPivot->customer_id][] = $promotion;
                }
                $customerSpecific = true;
            }
            if ($promotion->customerGroupsPivot->count()) {
                foreach ($promotion->customerGroupsPivot as $customerGroupPivot) {
                    $groupPromotions[$customerGroupPivot->group_id][] = $promotion;
                }
                $customerSpecific = true;
            }
            if (!$customerSpecific) {
                $globalPromotions[] = $promotion;
            }
        }

        // global prices (must be first to get groupId 0 prices)
        $this->_pushPriceData($searchIndexData, $globalPromotions, $product, null, null);
        // group specific
        foreach ($groupPromotions as $groupId => $promotions) {
            $groupAndGlobalPromotions = array_merge($globalPromotions, $promotions);
            usort($groupAndGlobalPromotions, [$this, 'sortPromotions']);
            $this->_pushPriceData($searchIndexData, $groupAndGlobalPromotions, $product, null, $groupId);
        }
        foreach ($product->advancedPricing as $advancedPrice) {
            if ($advancedPrice->group_id && !array_key_exists($advancedPrice->group_id, $groupPromotions)) {
                $this->_pushPriceData($searchIndexData, $globalPromotions, $product, null, $advancedPrice->group_id);
            }
        }
        // customer specific
        if ($customerPromotions) {
            $customerGroupIds = $this->_getCustomerGroupIds();
            foreach ($customerPromotions as $customerId => $promotions) {
                $customerAndGlobalPromotions = array_merge($globalPromotions, $promotions);
                if (array_key_exists($customerGroupIds[$customerId], $groupPromotions)) {
                    $customerAndGlobalPromotions = array_merge($customerAndGlobalPromotions, $groupPromotions[$customerGroupIds[$customerId]]);
                }
                usort($customerAndGlobalPromotions, [$this, 'sortPromotions']);
                $this->_pushPriceData($searchIndexData, $customerAndGlobalPromotions, $product, $customerId, $customerGroupIds[$customerId]);
            }
        }
    }

    /**
     * @return array
     */
    protected function _getCustomerGroupIds()
    {
        if (is_null($this->_customerGroupIds)) {
            $allCustomerIds = Promotion\CustomersPivot::pluck('customer_id')->toArray();
            $this->_customerGroupIds = Customer::whereIn('id', $allCustomerIds)->pluck('group_id', 'id')->toArray();
        }
        return $this->_customerGroupIds;
    }

    /**
     * @param Product $product
     * @param int $groupId
     * @return array
     */
    protected function _getGroupBasePrice($product, $groupId = 0)
    {
        if (!array_key_exists($product->id, $this->_basePrices)) {
            $this->_basePrices[$product->id] = [];
        }
        if (!array_key_exists($groupId, $this->_basePrices[$product->id])) {
            if ($product->advancedPricing->count() || !$groupId) {
                Product::setIndexPriceGroup($groupId);
                if ($product->variations->count()) {
                    $minPrice = null;
                    $maxPrice = null;
                    foreach ($product->variations as $variation) {
                        /** @var Product\Variation $variation */
                        $minPrice = is_null($minPrice) ? $variation->basePrice() : min($variation->basePrice(), $minPrice);
                        $maxPrice = is_null($maxPrice) ? $variation->basePrice() : max($variation->basePrice(), $maxPrice);
                    }
                } else {
                    $minPrice = $product->basePrice();
                    $maxPrice = $minPrice;
                }
                Product::setIndexPriceGroup(null);
                $this->_basePrices[$product->id][$groupId] = [$minPrice, $maxPrice];
            } else {
                $groupId = 0;
            }
        }
        return $this->_basePrices[$product->id][$groupId];
    }

    /**
     * @param Collection $searchIndexData
     * @param Promotion[] $applyPromotions
     * @param Product $product
     * @param int $customerId
     * @param int $groupId
     */
    protected function _pushPriceData($searchIndexData, $applyPromotions, $product, $customerId, $groupId)
    {
        $expires = null;
        list($minPrice, $maxPrice) = $this->_getGroupBasePrice($product, $groupId ?: 0);
        foreach ($applyPromotions as $promotion) {
            $minPrice = $promotion->applyDiscount($minPrice);
            $maxPrice = $promotion->applyDiscount($maxPrice);
            if ($promotion->active_to) {
                $expires = $expires ? ($promotion->active_to->lt($expires) ? $promotion->active_to : $expires) : $promotion->active_to;
            }
            if ($promotion->is_last) {
                break;
            }
        }
        $searchIndexData->push([
            'product_id' => $product->id,
            'min_price' => $minPrice,
            'max_price' => $maxPrice,
            'group_id' => $groupId,
            'customer_id' => $customerId,
            'expires' => $expires
        ]);
    }

    /**
     * @param Promotion $a
     * @param Promotion $b
     * @return int
     */
    protected function sortPromotions($a, $b)
    {
        return $a->priority <=> $b->priority;
    }

}

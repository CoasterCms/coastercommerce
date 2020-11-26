<?php namespace CoasterCommerce\Core\Listeners;

use CoasterCommerce\Core\Events\AdminProductSave as AdminProductSaveEvent;
use CoasterCommerce\Core\Model\Product\AdvancedPricing;
use CoasterCommerce\Core\Model\Product\Variation;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\Factory;
use Illuminate\Validation\ValidationException;

class AdminProductSave
{

    /**
     * @var Factory
     */
    protected $_validator;

    /**
     * AdminProductSave constructor.
     * @param Factory $validation
     */
    public function __construct(Factory $validation)
    {
        $this->_validator = $validation;
    }

    /**
     * @param AdminProductSaveEvent $event
     * @throws ValidationException
     */
    public function handle(AdminProductSaveEvent $event)
    {
        // save categories
        $event->product->categories()->sync(
            array_key_exists('category_ids', $event->inputData) ? $event->inputData['category_ids'] : []
        );

        // save advanced pricing
        $advancingPricingData = request()->post('advanced_pricing', []);
        $rules = [];
        foreach ($advancingPricingData as $k => &$advancingPricing) {
            $advancingPricingData[$k]['price'] = preg_replace('/[^\d.]/', '', $advancingPricingData[$k]['price']) ?: null;
            $rules += ['advanced_pricing.'.$k.'.min_quantity' => 'nullable|numeric|min:0', 'advanced_pricing.'.$k.'.price' => 'numeric|min:0'];
        }
        if ($rules) {
            $this->_validator->validate(['advanced_pricing' => $advancingPricingData], $rules);
        }
        $event->product->advancedPricing()->delete();
        foreach ($advancingPricingData as $advancingPricing) {
            $advancedPricingModel = new AdvancedPricing();
            $advancedPricingModel->product_id = $event->product->id;
            $advancedPricingModel->group_id = $advancingPricing['group_id'] ?: null;
            $advancedPricingModel->min_quantity = $advancingPricing['min_quantity'] ?: 0;
            $advancedPricingModel->price = $advancingPricing['price'];
            $advancedPricingModel->save();
        }

        // save related products
        $relatedProducts = request()->post('related_product', []);
        $relatedProductSync = [];
        foreach ($relatedProducts as $relatedProduct) {
            if (array_key_exists('relation', $relatedProduct)) {
                $relatedProductSync[$relatedProduct['related_product_id']] = array_fill_keys($relatedProduct['relation'], 1);
            }
        }
        $event->product->relatedProducts()->sync($relatedProductSync);

        // save product variations
        $productVariations = request()->post('variations', []);
        $rules = [];
        foreach ($productVariations as $k => $productVariation) {
            $productVariations[$k]['price'] = preg_replace('/[^\d.]/', '', $productVariation['price']) ?: null;
            $productVariations[$k]['fixed_price'] = array_key_exists('fixed_price', $productVariation) ? $productVariation['fixed_price'] : 0;
            if ($productVariations[$k]['fixed_price'] && !$productVariations[$k]['price']) {
                $productVariations[$k]['price'] = 0;
            }
            $rules += ['variations.'.$k.'.stock_qty' => 'nullable|numeric|min:0', 'variations.'.$k.'.weight' => 'nullable|numeric|min:0', 'variations.'.$k.'.price' => 'nullable|numeric'];
        }
        if ($rules) {
            $this->_validator->validate(['variations' => $productVariations], $rules);
        }
        /** @var Collection $existingVariations */
        $productVariationIds = [];
        $existingVariations = $event->product->variations->keyBy('id');
        $defaultInputCols = array_fill_keys(['enabled', 'sku', 'price', 'fixed_price', 'stock_qty', 'weight', 'image'], null);
        $sortValue = 0;
        foreach ($productVariations as $i => $productVariation) {
            $productVariationModel = $existingVariations->offsetExists($i) ? $existingVariations->offsetGet($i) : new Variation();
            $productVariationModel->sort_value = ++$sortValue;
            $productVariationModel->product_id = $event->product->id;
            $productVariationModel->variation = json_encode(array_diff_key($productVariation, $defaultInputCols));
            $productVariationModel->forceFill(array_intersect_key($productVariation, $defaultInputCols))->save();
            $productVariationIds[] = $productVariationModel->id;
        }
        $event->product->variations()->whereNotIn('id', $productVariationIds)->delete();
    }

}


<?php namespace CoasterCommerce\Core\Listeners;

use CoasterCommerce\Core\Events\AdminProductMassUpdate as AdminProductMassUpdateEvent;
use CoasterCommerce\Core\Model\CategoryProducts;

class AdminProductMassUpdate
{

    /**
     * @param AdminProductMassUpdateEvent $event
     */
    public function handle(AdminProductMassUpdateEvent $event)
    {
        // save mass update on category attribute
        if (array_key_exists('category_ids', $event->inputData)) {
            $productIds = $event->products->pluck('id')->toArray();
            CategoryProducts::whereIn('product_id', $productIds)->delete();
            $categoryProducts = collect();
            foreach ($productIds as $productId) {
                foreach ($event->inputData['category_ids'] as $categoryId) {
                    $categoryProducts->push([
                        'product_id' => $productId,
                        'category_id' => $categoryId,
                    ]);
                }
            }
            $categoryProducts->chunk(100)->each(function ($categoryProductChunk) {
                CategoryProducts::insert($categoryProductChunk->toArray());
            });
        }
    }

}


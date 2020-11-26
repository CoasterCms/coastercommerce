<?php namespace CoasterCommerce\Core\Listeners;

use CoasterCommerce\Core\Events\AdminProductGroupAttributes as AdminProductGroupAttributesEvent;
use CoasterCommerce\Core\Model\Product\Attribute;

class AdminProductGroupAttributes
{

    /**
     * @param AdminProductGroupAttributesEvent $event
     */
    public function handle(AdminProductGroupAttributesEvent $event)
    {
        if ($event->group->name == 'Advanced Pricing') {
            $advancedPricingAttribute = new Attribute();
            $advancedPricingAttribute->frontend = 'text'; // fronted renderer text
            $advancedPricingAttribute->code = 'advancedPricing'; // product model attribute/relation
            $advancedPricingAttribute->view = 'coaster-commerce::admin.product.attribute-input.advanced-pricing'; // view
            $event->attributes->push($advancedPricingAttribute);
        }
        if ($event->group->name == 'Related Products') {
            $advancedPricingAttribute = new Attribute();
            $advancedPricingAttribute->frontend = 'text'; // fronted renderer text
            $advancedPricingAttribute->code = 'relatedProducts'; // product model attribute/relation
            $advancedPricingAttribute->view = 'coaster-commerce::admin.product.attribute-input.related-products'; // view
            $event->attributes->push($advancedPricingAttribute);
        }
    }

}


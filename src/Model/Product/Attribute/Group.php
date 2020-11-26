<?php

namespace CoasterCommerce\Core\Model\Product\Attribute;

use CoasterCommerce\Core\Events\AdminProductGroupAttributes;
use CoasterCommerce\Core\Model\Product\Attribute;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Group extends Model
{

    public $table = 'cc_product_attribute_groups';

    /**
     * @return BelongsToMany
     */
    public function productAttributes()
    {
        return $this->belongsToMany(Attribute::class, 'cc_product_attribute_group_items')->orderBy('position');
    }

    /**
     * @return Collection
     */
    public function adminProductAttributes()
    {
        $productAttributes = collect();
        foreach ($this->productAttributes as $productAttribute) {
            /** @var Attribute $productAttribute */
            $productAttributes->push($productAttribute);
        }
        event(new AdminProductGroupAttributes($productAttributes, $this));
        return $productAttributes;
    }

    /**
     * @return string
     */
    public function tabName()
    {
        return strtolower(preg_replace('/[^\da-z]+/i', '', $this->name));
    }

}

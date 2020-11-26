<?php namespace CoasterCommerce\Core\Events;

use CoasterCommerce\Core\Model\Product\Attribute\Group;
use Illuminate\Support\Collection;

class AdminProductGroupAttributes
{

    /**
     * @var Collection
     */
    public $attributes;

    /**
     * @var Group
     */
    public $group;

    /**
     * AdminProductGroupAttributes constructor.
     * @param Collection $attributes
     * @param Group $group
     */
    public function __construct(Collection $attributes, Group $group)
    {
        $this->attributes = $attributes;
        $this->group = $group;
    }

}


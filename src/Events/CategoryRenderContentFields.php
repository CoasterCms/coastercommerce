<?php namespace CoasterCommerce\Core\Events;

use CoasterCommerce\Core\Model\Category;

class CategoryRenderContentFields
{

    /**
     * @var Category
     */
    public $category;

    /**
     * @var string
     */
    public $html;

    /**
     * CategoryRenderContentFields constructor.
     * @param Category $category
     */
    public function __construct(Category $category)
    {
        $this->category = $category;
        $this->html = '';
    }

    public function getHtml()
    {
        return $this->html;
    }

}


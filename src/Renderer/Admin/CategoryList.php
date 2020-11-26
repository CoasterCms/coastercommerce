<?php

namespace CoasterCommerce\Core\Renderer\Admin;


use CoasterCommerce\Core\Model\Category;

class CategoryList
{

    /**
     * @var Category[]
     */
    protected $_categories;

    /**
     * @var array
     */
    protected $_subCatIds;

    /**
     * @var int
     */
    protected $_rootId;

    /**
     * CategoryList constructor.
     * @param $rootId
     */
    public function __construct($rootId)
    {
        $this->_rootId = $rootId;
        $categories = Category::all();
        $this->_subCatIds = [];
        foreach ($categories as $category) {
            if ($category->path) {
                $pathIds = explode('/', $category->path);
                $this->_subCatIds[end($pathIds)][] = $category->id;
            } else {
                $this->_subCatIds[0][] = $category->id;
            }
            $this->_categories[$category->id] = $category;
        }
        $categories = $this->_categories;
        foreach ($this->_subCatIds as $k => $subCatIds) {
            $this->_subCatIds[$k] = array_unique($subCatIds);
            usort($this->_subCatIds[$k], function ($a, $b) use($categories) {
                return $categories[$a]->position <=> $categories[$b]->position;
            });
        }
    }

    /**
     * @return string
     */
    public function rootItems()
    {
        return $this->category(null);
    }

    /**
     * @param Category $category
     * @return string
     */
    public function category(Category $category = null)
    {
        $renderedSubCats = '';
        if ($subCats = $this->subCats($category)) {
            foreach ($subCats as $subCat) {
                $renderedSubCats .= view('coaster-commerce::admin.category.list.category', [
                    'rootCategory' => $subCat,
                    'listRenderer' => $this
                ]);
            }
        }
        return $renderedSubCats;
    }

    /**
     * @param Category|null $category
     * @return array
     */
    public function subCats(Category $category = null)
    {
        $subCats = [];
        $catId = $category ? $category->id : 0;
        if (array_key_exists($catId, $this->_subCatIds)) {
            foreach ($this->_subCatIds[$catId] as $subCatId) {
                $subCats[] = $this->_categories[$subCatId];
            }
        }
        return $subCats;
    }

}

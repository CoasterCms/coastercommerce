<?php namespace CoasterCommerce\Core\CatalogueUrls;

use CoasterCommerce\Core\Model\Category;
use CoasterCommerce\Core\Model\CategoryProducts;
use CoasterCommerce\Core\Model\Product;

/**
 * Class CatalogueUrls, used in admin to check conflicts for product/category url_key updates
 * @package CoasterCommerce\Core\CatalogueUrls
 */
class CatalogueUrls
{

    /**
     * @var array
     */
    protected $_prodUrls;

    /**
     * @var array
     */
    protected $_catUrls;

    /**
     * @var
     */
    protected $_subCats;

    /**
     * @var
     */
    protected $_catParentIds;

    /**
     * @var
     */
    protected $_catIsAnchor;

    /**
     * @var array
     */
    protected $_prodCats;

    /**
     * @var array
     */
    protected $_catProds;

    /**
     * CatalogueUrls constructor.
     * Load urls from prod and cat tables as well as cat tree and prod/cat relations
     */
    public function __construct()
    {
        $this->_prodUrls = (new Product)->newModelQuery()->select(['url_key', 'id'])->get()->pluck('url_key', 'id')->toArray();
        $categories = Category::select(['url_key', 'id', 'anchor', 'path'])->get();
        $this->_catUrls = $categories->pluck('url_key', 'id')->toArray();
        $this->_subCats = [];
        $this->_catParentIds = [];
        $this->_catIsAnchor = [];
        foreach ($categories as $category) {
            $this->_catIsAnchor[$category->id] = $category->anchor;
            if ($parentId = $category->parentId()) {
                $this->_subCats[$parentId][] = $category->id;
                $this->_catParentIds[$category->id] = $parentId;
            } else {
                $this->_subCats[0][] = $category->id;
            }
        }
        $this->_prodCats = [];
        $this->_catProds = [];
        $categoryProducts = CategoryProducts::all();
        foreach ($categoryProducts as $categoryProduct) {
            $this->_prodCats[$categoryProduct->product_id][] = $categoryProduct->category_id;
            $this->_catProds[$categoryProduct->category_id][] = $categoryProduct->product_id;
        }
    }

    /**
     * @param int $id
     * @param string $newUrl
     * @param array $newCategoryIds
     * @return array
     */
    public function productUrlConflicts($id, $newUrl, $newCategoryIds = [])
    {
        $conflicts = [];
        foreach ($this->_prodUrls as $prodId => $productUrl) {
            if ($newUrl == $productUrl && $id != $prodId) {
                $conflicts[] = 'Same url_key as product ID ' . $prodId;
            }
        }
        $prodCategoryIds = is_null($newCategoryIds) ? (array_key_exists($id, $this->_prodCats) ? $this->_prodCats[$id] : []) : $newCategoryIds;
        $prodCategoryIds[] = 0; // check against root cat urls
        foreach ($prodCategoryIds as $prodCategoryId) {
            if (array_key_exists($prodCategoryId, $this->_subCats)) {
                foreach ($this->_subCats[$prodCategoryId] as $subCatId) {
                    if ($newUrl == $this->_catUrls[$subCatId]) {
                        $conflicts[] = 'Same url_key as category ID ' . $subCatId;
                    }
                }
            }
        }
        return $conflicts;
    }

    /**
     * @param int $id
     * @param string $newUrl
     * @param int $newParentId
     * @return array
     */
    public function categoryUrlConflicts($id, $newUrl, $newParentId = null)
    {
        $conflicts = [];
        $parentCatId = $newParentId ?: (array_key_exists($id, $this->_catParentIds) ? $this->_catParentIds[$id] : 0);
        // load all cats under same parent and check url_key conflicts
        if (array_key_exists($parentCatId, $this->_subCats)) {
            foreach ($this->_subCats[$parentCatId] as $subCatId) {
                if ($newUrl == $this->_catUrls[$subCatId] && $subCatId != $id) {
                    $conflicts[] = 'Same url_key as category ID ' . $subCatId;
                }
            }
        }
        // load all products in parent category and check url_key conflicts
        if ($parentCatId) {
            $productIds = [];
            $loadSubCats = $this->_catIsAnchor[$parentCatId] ? $this->_recursiveSubCats($parentCatId) : [$parentCatId];
            foreach ($loadSubCats as $subCatId) {
                if (array_key_exists($subCatId, $this->_catProds)) {
                    $productIds = array_merge($this->_catProds[$subCatId], $productIds);
                }
            }
            $productIds = array_unique($productIds);
        } else {
            $productIds = array_keys($this->_prodUrls);
        }
        foreach ($productIds as $sameLevelProdId) {
            if ($newUrl == $this->_prodUrls[$sameLevelProdId]) {
                $conflicts[] = 'Same url_key as product ID ' . $sameLevelProdId;
            }
        }
        return $conflicts;
    }

    /**
     * @param int $catId
     * @param int $depth
     * @return array
     */
    protected function _recursiveSubCats($catId, $depth = 1)
    {
        $subCatIds = [];
        if (array_key_exists($catId, $this->_subCats)) {
            foreach ($this->_subCats[$catId] as $subCatId) {
                $subCatIds[] = $subCatId;
                if ($depth < 50) { // prevent loops
                    $subCatIds = array_merge($this->_recursiveSubCats($subCatId, ++$depth), $subCatIds);
                }
            }
        }
        return $subCatIds;
    }

    /**
     * @param int $id
     * @param string $url
     */
    public function setCategoryUrl($id, $url)
    {
        $this->_catUrls[$id] = $url;
    }

    /**
     * @param int $id
     * @param string $url
     */
    public function setProductUrl($id, $url)
    {
        $this->_prodUrls[$id] = $url;
    }

    /**
     * @param int $id
     * @param array $categoryIds
     */
    public function setProductCategories($id, $categoryIds)
    {
        $oldCatIds = array_key_exists($id, $this->_prodCats) ? $this->_prodCats[$id] : [];
        $this->_prodCats[$id] = $categoryIds;
        foreach ($oldCatIds as $oldCatId) {
            if (($key = array_search($id, $this->_catProds[$oldCatId])) !== false) {
                unset($this->_catProds[$oldCatId][$key]);
            }
        }
        foreach ($categoryIds as $categoryId) {
            $this->_catProds[$categoryId][] = $id;
        }
    }

    /**
     * @param int $id
     * @param array $productIds
     */
    public function setCategoryProducts($id, $productIds)
    {
        $oldProdIds = $this->_catProds[$id];
        $this->_catProds[$id] = $productIds;
        foreach ($oldProdIds as $oldProdId) {
            if (($key = array_search($id, $this->_prodCats[$oldProdId])) !== false) {
                unset($this->_prodCats[$oldProdId][$key]);
            }
        }
        foreach ($productIds as $productId) {
            $this->_prodCats[$productId][] = $id;
        }
    }

    /**
     * @param int $categoryId
     * @param int $subCatId
     */
    public function addSubCatId($categoryId, $subCatId)
    {
        $this->_subCats[$categoryId][] = $subCatId;
    }

    /**
     * @param int $categoryId
     * @param int $anchor
     */
    public function setAnchor($categoryId, $anchor)
    {
        $this->_catIsAnchor[$categoryId] = $anchor;
    }

}



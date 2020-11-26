<?php

namespace CoasterCommerce\Core\Model;

use CoasterCommerce\Core\CatalogueUrls\CatalogueUrls;
use CoasterCommerce\Core\CatalogueUrls\UrlResolver;
use CoasterCommerce\Core\Database\AttributeBuilder;
use CoasterCommerce\Core\Model\Product\Attribute\Model\FileModel\FileValue;
use CoasterCommerce\Core\Model\Product\SearchIndex;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Category extends Model
{

    public $table = 'cc_categories';

    /**
     * @var array
     */
    protected $_cachedProductIds;

    /**
     * @var array
     */
    protected $_cachedFilteredProductIds;

    /**
     * Gets directly assigned products
     * @return BelongsToMany
     */
    public function products()
    {
        return $this->belongsToMany(Product::class, 'cc_category_products');
    }

    /**
     * Returns all categories anchored to this category including itself as array of ids
     * @return array
     */
    public function getAnchoredCategoryIds()
    {
        $anchoredCatIds = [$this->id];
        if ($this->anchor) {
            $anchoredCatIds = array_merge(
                $anchoredCatIds,
                static::where('path', 'LIKE', $this->fullPath() . '/%')->orWhere('path', $this->fullPath())
                    ->pluck('id')->toArray()
            );
        }
        return $anchoredCatIds;
    }

    /**
     * Return all products ids in category (including subcat products if set as an anchor)
     * @param bool $forceRefresh
     * @return array
     */
    public function getProductIds($forceRefresh = false)
    {
        if (!isset($this->_cachedProductIds) || $forceRefresh) {
            $productIds = CategoryProducts::whereIn('category_id', $this->getAnchoredCategoryIds())->pluck('product_id')->toArray();
            $this->_cachedProductIds = array_unique($productIds);
        }
        return $this->_cachedProductIds;
    }

    /**
     * Return all products ids in category (same as above but with active filters)
     * @param bool $forceRefresh
     * @return array
     */
    public function getFilteredProductIds($forceRefresh = false)
    {
        if (!isset($this->_cachedFilteredProductIds) || $forceRefresh) {
            $this->_cachedFilteredProductIds = (new SearchIndex())->filterResults($this->getProductIds($forceRefresh), request()->query());
        }
        return $this->_cachedFilteredProductIds;
    }

    /**
     * Return all enabled products in category
     * @return AttributeBuilder
     */
    public function getProducts()
    {
        // variations and categories needed for prices so may as well eager load
        return Product::with(['variations', 'categories'])->where('enabled', 1)->whereIn('id', $this->getProductIds());
    }

    /**
     * Return all enabled products in category (same as above but with active filters)
     * @return AttributeBuilder
     */
    public function getFilteredProducts()
    {
        return Product::with(['variations', 'categories'])->where('enabled', 1)->whereIn('id', $this->getFilteredProductIds());
    }

    /**
     * @return Collection|static[]
     */
    public function getCategories()
    {
        return (new static)->where('path', $this->fullPath())->get();
    }

    /**
     * Full category path including itself
     * @return string
     */
    public function fullPath()
    {
        return ($this->path ? $this->path . '/' : '') . $this->id;
    }

    /**
     * @return int
     */
    public function parentId()
    {
        $pathIds = $this->path ? explode('/', $this->path) : [];
        return $pathIds ? (int) end($pathIds) : 0;
    }

    /**
     * @return array
     */
    public function parentIds()
    {
        return $this->path ? explode('/', $this->path) : [];
    }

    /**
     * Uses images attribute, also returns default
     * @return string
     */
    public function getImage()
    {
        $firstImage = $this->images ? $this->getImages()->getFile(0) : null;
        if ($firstImage) {
            return $firstImage;
        }
        $defaultImages = [
            '/uploads/catalogue/category/_default/image.jpg',
            '/uploads/catalogue/category/_default/image.png'
        ];
        foreach ($defaultImages as $defaultImage) {
            if (file_exists(public_path($defaultImage))) {
                return $defaultImage;
            }
        }
        return 'https://dummyimage.com/600x400/000/fff&text=NoImage';
    }

    /**
     * @return FileValue
     */
    public function getImages()
    {
        return new FileValue($this->images);
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        $url = '';
        /** @var UrlResolver $urlResolver */
        $urlResolver = app('coaster-commerce.url-resolver');
        foreach (explode('/', $this->fullPath()) as $catId) {
            $url .= '/' . $urlResolver->getCatUrlKey($catId);
        }
        return $url;
    }

    /**
     * @return string
     */
    public function generateUniqueKey()
    {
        $base_url_key = strtolower(preg_replace('/[^\da-z]+/i', '-', $this->name));
        $url_key = $base_url_key;

        $i = 0;

        /** @var CatalogueUrls $catalogueUrls */
        $catalogueUrls = app('coaster-commerce.catalog-urls');
        $pathIds = explode('/', $this->path);
        $parentId = $pathIds ? end($pathIds) : null;

        while ($conflicts = $catalogueUrls->categoryUrlConflicts($this->id, $url_key, $parentId)) {
            $url_key = $base_url_key . '-' . ++$i;
        }

        return $url_key;
    }

    /**
     * @param array $options
     * @return bool
     */
    public function save(array $options = [])
    {
        if (!$this->url_key) {
            $this->url_key = $this->generateUniqueKey();
        }

        $this->images = ($this->images && is_a($this->images, FileValue::class)) ? $this->images->toJson() : $this->images;

        $did_save = parent::save($options);
        /** @var CatalogueUrls $catalogueUrls */
        $catalogueUrls = app('coaster-commerce.catalog-urls');
        
        $catalogueUrls->setCategoryUrl($this->id, $this->url_key);

        return $did_save;
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function __get($key)
    {
        if ($key == 'getProducts') {
            return $this->getProducts()->orderBy('name')->get();
        }
        return parent::__get($key);
    }

}

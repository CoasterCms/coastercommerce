<?php namespace CoasterCommerce\Core\CatalogueUrls;

use CoasterCommerce\Core\Events\FrontendInit;
use CoasterCommerce\Core\Menu\FrontendCrumb;
use CoasterCommerce\Core\Menu\FrontendCrumbs;
use CoasterCommerce\Core\Model\Category;
use CoasterCommerce\Core\Model\Product;
use CoasterCommerce\Core\Model\Redirect;
use CoasterCommerce\Core\Model\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Class UrlResolver, used on frontend to check for active product/category and to generate links
 * Also generates metas and breadcrumbs
 * @package CoasterCommerce\Core\CatalogueUrls
 */
class UrlResolver
{

    /**
     * @var Redirect
     */
    protected $_redirect;

    /**
     * @var Category
     */
    protected $_category;

    /**
     * @var Product
     */
    protected $_product;

    /**
     * @var array array
     */
    protected $_catIdsByUrl;

    /**
     * @var array array
     */
    protected $_catUrlsById;

    /**
     * @var array
     */
    protected $_subCats;

    /**
     * @var array
     */
    protected $_catIsAnchor;

    /**
     * @var bool
     */
    protected $_isCommerce;

    /**
     * @var FrontendCrumb[]
     */
    protected $_customCrumbs;

    /**
     * @var string
     */
    protected $_customTitle;

    /**
     * @var string
     */
    protected $_customDescription;

    /**
     * @var string
     */
    protected $_customKeywords;

    /**
     * @var View
     */
    protected $_view;

    /**
     * CatalogueUrls constructor.
     */
    public function __construct()
    {
        $categories = Category::select(['name', 'url_key', 'id', 'anchor', 'path'])->orderBy('position', 'asc')->get();
        $this->_catIdsByUrl = [];
        foreach ($categories as $category) {
            $pathArray = explode('/', $category->path);
            $this->_catIdsByUrl[$category->url_key . '#' . ($pathArray ? end($pathArray) : '')] = $category->id;
        }
        $this->_catUrlsById = $categories->pluck('url_key', 'id')->toArray();
        foreach ($categories as $category) {
            $this->_catIsAnchor[$category->id] = $category->anchor;
            if ($parentId = $category->parentId()) {
                $this->_subCats[$parentId][] = $category->id;
            } else {
                $this->_subCats[0][] = $category->id;
            }
        }
        $this->_view = app('view');
        if (config('coaster-commerce.autoload-pb')) {
            app('pageBuilder');
        }
    }

    /**
     * @param FrontendCrumb[] $crumbs
     * @param string $title
     * @param string $description
     * @param string $keywords
     * @return $this
     */
    public function setCustomPage($crumbs, $title, $description, $keywords)
    {
        $this->_customCrumbs = $crumbs;
        $this->_customTitle = $title;
        $this->_customDescription = $description;
        $this->_customKeywords = $keywords;
        return $this;
    }

    /**
     * loads data for generateResponse()
     * @param Request $request
     * @return bool
     */
    public function isCommerceUrl(Request $request)
    {
        if ($segments = $request->segments()) {
            $this->_resolveCategory($segments);
            if (count($segments) == 1) {
                $this->_resolveProduct($segments);
            }
            $this->_isCommerce = !count($segments);
        }
        if (!$this->_isCommerce && $redirect = Redirect::where('url', $request->path())->first()) {
            $this->_redirect = $redirect;
            $this->_isCommerce = true;
        }
        event(new FrontendInit($this));
        return $this->_isCommerce;
    }

    /**
     * @param int $catId
     * @param callable $callback
     * @param int $depth
     * @return string
     */
    public function subCatsList($catId, $callback, $depth = 1)
    {
        $output = '';
        if (array_key_exists($catId, $this->_subCats)) {
            foreach ($this->_subCats[$catId] as $subCatId) {
                if ($depth < 50) { // prevent loops
                    $output .= $callback(
                        $subCatId,
                        $this->subCatsList($subCatId, $callback, ++$depth)
                    );
                }
            }
        }
        return $output;
    }

    /**
     * @param array $segments
     */
    protected function _resolveCategory(&$segments)
    {
        $parentId = 0;
        foreach ($segments as $k => $urlSegment) {
            $urlSegment .= '#' . ($parentId ?: '');
            $categoryId = array_key_exists($urlSegment, $this->_catIdsByUrl) ? $this->_catIdsByUrl[$urlSegment] : 0;
            if ($categoryId && in_array($categoryId, $this->_subCats[$parentId])) {
                $parentId = $categoryId;
                unset($segments[$k]);
            } else {
                break;
            }
        }
        if ($parentId) {
            $this->_category = Category::where('id', $parentId)->where('enabled', 1)->first();
        }
    }

    /**
     * @param array $segments
     */
    protected function _resolveProduct(&$segments)
    {
        if ($product = Product::where('url_key', end($segments))->where('enabled', 1)->first()) {
            if ($this->_category) {
                $categoryPathIds = explode('/', $this->_category->fullPath());
                // only check if product is in lowest level category or anchor cats
                $checkInCatIds = [end($categoryPathIds)];
                foreach ($categoryPathIds as $categoryPathId) {
                    if ($this->_catIsAnchor[$categoryPathId]) {
                        $checkInCatIds = array_merge($checkInCatIds, $this->_recursiveSubCats($categoryPathId));
                        break;
                    }
                }
                if (!array_intersect($product->categories->pluck('id')->toArray(), $checkInCatIds)) {
                    return;
                }
            }
            $segments = [];
            $this->_product = $product;
        }
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
     * @return Response|RedirectResponse
     */
    public function generateResponse()
    {
        if ($this->_redirect) {
            return $this->_redirect->response();
        }
        $this->_view->share('category', $this->_category);
        $this->_view->share('product', $this->_product);
        if (!$this->_product && config('coaster-commerce.single-product-redirect')) {
            if ($this->_category && $this->_category->getProducts()->count() == 1) {
                $product = $this->_category->getProducts()->first();
                return redirect($product->getUrl());
            }
        }
        if ($this->_product) {
            return response($this->_view->make(config('coaster-commerce.views') . 'templates.product'), 200);
        } elseif ($this->_category) {
            $pageSize = Setting::getValue('catalogue_pagination') ?: 30;
            return response($this->_view->make(config('coaster-commerce.views') . 'templates.category', [
                'pageSize' => $pageSize
            ]), 200);
        } else {
            return response($this->_view->make(config('coaster-commerce.views') . 'errors.404'), 404);
        }
    }

    /**
     * Url store for category urls, always initialized on frontend so can save additional queries
     * @param int $catId
     * @return string
     */
    public function getCatUrlKey($catId)
    {
        return array_key_exists($catId, $this->_catUrlsById) ? $this->_catUrlsById[$catId] : null;
    }

    /**
     * Loads only category/product crumbs (no cms crumb support currently)
     * @return FrontendCrumbs
     */
    public function loadCrumbs()
    {
        $crumbs = [new FrontendCrumb('Home', '/')];

        if ($this->_customCrumbs) {
            $crumbs = array_merge($crumbs, $this->_customCrumbs);
        } else {
            if ($this->_category) {
                $pathIds = explode('/', $this->_category->fullPath());
                foreach ($pathIds as $pathId) {
                    $category = ($this->_category->id == $pathId) ? $this->_category : Category::find($pathId);
                    $crumbs[] = new FrontendCrumb($category->name, $category->getUrl());
                }
            }
            if ($this->_product) {
                $crumbs[] = new FrontendCrumb($this->_product->name, $this->_product->getUrl($this->_category));
            } elseif (!$this->_isCommerce) {
                $crumbs[] = new FrontendCrumb('404', '');
            }
        }

        $lastCrumb = end($crumbs);
        $lastCrumb->active = true;

        return new FrontendCrumbs($crumbs);
    }

    /**
     * @return \stdClass
     */
    public function loadMetas()
    {
        $entity = $this->_product ?: $this->_category;
        $metas = new \stdClass();
        if ($this->_customTitle) {
            $metas->title = $this->_customTitle;
        } else {
            $metas->title = $entity ? ($entity->meta_title ?: $entity->name) : null;
        }
        if ($this->_customDescription) {
            $metas->description = $this->_customDescription;
        } else {
            $metas->description = $entity ? $this->_htmlToMeta($entity->meta_description ?: $entity->description ?: $entity->content) : null;
        }
        if ($this->_customKeywords) {
            $metas->keywords = $this->_customKeywords;
        } else {
            $metas->keywords = $entity ? $entity->meta_keywords : null;
        }
        return $metas;
    }

    /**
     * @param string $html
     * @param int $len
     * @return string
     */
    protected function _htmlToMeta($html, $len = 255)
    {
        $html = str_replace(PHP_EOL, ' ', $html);
        $html = strip_tags(html_entity_decode($html, ENT_QUOTES, 'UTF-8'));
        $html = trim(preg_replace('/\s+/', ' ', $html));
        return Str::cutString($html, $len);
    }

    /**
     * @return Product
     */
    public function getProduct()
    {
        return $this->_product;
    }

    /**
     * @return Category
     */
    public function getCategory()
    {
        return $this->_category;
    }

}

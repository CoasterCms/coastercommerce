<?php namespace CoasterCommerce\Core\Menu;

use CoasterCms\Facades\PageBuilder;
use CoasterCms\Helpers\Cms\Page\Path;
use CoasterCms\Models\Menu;
use CoasterCms\Models\Page;
use CoasterCms\Models\PageLang;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\View\View;

class FrontendMenu
{

    /**
     * @var
     */
    protected $_activeParentIds;

    /**
     * @var FrontendItem[]
     */
    protected $_items = [];

    /**
     * Custom data supplied on creation
     * @var array
     */
    protected $_data;

    /**
     * @var callable
     */
    protected $_preRenderFn;

    /**
     * FrontendMenu constructor.
     * @param array $data
     */
    public function __construct($data = [])
    {
        $this->_data = $data;
    }

    /**
     * @param mixed $key
     * @return array|mixed|null
     */
    public function getData($key = null)
    {
        if (is_null($key)) {
            return $this->_data;
        }
        return array_key_exists($key, $this->_data) ? $this->_data[$key] : null;
    }

    /**
     * @param callable|null $callable
     */
    public function setPreRenderFn($callable = null)
    {
        $this->_preRenderFn = $callable;
    }

    /**
     * @param FrontendItem $item
     * @return $this
     */
    public function addItem($item)
    {
        $this->_items[] = $item;
        return $this;
    }

    /**
     * @return FrontendItem[]
     */
    public function getItems()
    {
        return $this->_items;
    }

    /**
     * @param $index
     */
    public function unsetItemAtIndex($index)
    {
        unset($this->_items[$index]);
    }

    /**
     * @param string $name
     */
    public function removeByName($name)
    {
        if (array_key_exists($name, $this->_items)) {
            unset($this->_items[$name]);
        }
    }

    /**
     * @param string $name
     * @return FrontendItem
     */
    public function getByName($name)
    {
        foreach ($this->_items as $item) {
            if ($item->name === $name) {
                return $item;
            }
        }
        return null;
    }

    /**
     * @return $this
     */
    public function loadCustomerMenu()
    {
        $this->_preRenderFn = function(FrontendMenu $menu) {
            // deferred to run in render function to make sure session is loaded
            if ($menu->getData('cart')->getCustomer()) {
                $menu
                    ->addItem((new FrontendItem(null, 'Account Details'))->route('coaster-commerce.frontend.customer.account.address')->setPosition(10))
                    ->addItem((new FrontendItem(null, 'My Orders'))->route('coaster-commerce.frontend.customer.account.order.list')->setPosition(20))
                    ->addItem((new FrontendItem(null, 'Change Password'))->route('coaster-commerce.frontend.customer.account.password')->setPosition(30));
            } else {
                $menu
                    ->addItem((new FrontendItem(null, 'Login'))->route('coaster-commerce.frontend.customer.login')->setPosition(10))
                    ->addItem((new FrontendItem(null, 'Register'))->route('coaster-commerce.frontend.customer.register')->setPosition(20));
            }
        };
        return $this;
    }

    /**
     * @param string $menuName
     * @return $this
     */
    public function loadCmsMenu($menuName)
    {
        if ($menu = Menu::preload($menuName)) {
            $this->loadCmsItems($menu->items()->get(), null);
        }
        return $this;
    }

    /**
     * @param Collection|array $items
     * @param FrontendItem $parentItem
     * @param null $subLevels
     */
    public function loadCmsItems($items, $parentItem, $subLevels = null)
    {
        foreach ($items as $order => $item) {

            if (is_a($item, Page::class)) {
                $newSubLevels = $subLevels - 1;
                $position = $order * 10;
                $page = $item;
                $customName = $parentItem ? $this->_cmsCustomName($page->id, $parentItem->options['custom_page_names']) : null;
            } else {
                $newSubLevels = $item->sub_levels;
                $position = $item->order * 10;
                $page = Page::preload($item->page_id);
                $customName = $item->custom_name;
            }

            if (($parentItem && $parentItem->options && $this->_cmsHidden($item->id, $parentItem->options['hidden_pages'])) || !$page->is_live()) {
                continue;
            }

            $parentPageId = $parentItem ? $parentItem->options['page']->id : null;
            $name = $customName ?: PageLang::getName($page->id);
            $url = ($page && $page->link) ? PageLang::getUrl($page->id) : Path::getFullUrl(Path::parsePageId($page->id, $parentPageId));
            $active = $this->_cmsActivePage($page->id);

            $frontendItem = new FrontendItem($parentItem, $name, $url, $active, $position, [
                'page' => $page,
                'parentPageId' => $parentPageId,
                'custom_page_names' => $parentItem ? $parentItem->options['custom_page_names'] : null,
                'hidden_pages' => $parentItem ? $parentItem->options['hidden_pages'] : null,
            ]);

            if ($newSubLevels > 0) {
                if ($subPages = Page::category_pages($page->id)) {
                    $this->loadCmsItems($subPages, $frontendItem, $subLevels);
                }
            }

            if (!$parentItem) {
                $this->addItem($frontendItem);
            }

        }
    }

    /**
     * @param int $pageId
     * @param string $customPageNames
     * @return string
     */
    protected function _cmsCustomName($pageId, $customPageNames)
    {
        $pageNames = @unserialize($customPageNames);
        if (is_array($pageNames) && array_key_exists($pageId, $pageNames)) {
            return $pageNames[$pageId];
        }
        return '';
    }

    /**
     * @param int $pageId
     * @param string $hiddenPages
     * @return bool
     */
    protected function _cmsHidden($pageId, $hiddenPages)
    {
        return $hiddenPages ? in_array($pageId, explode(',', $hiddenPages)) : false;
    }

    /**
     * @param $pageId
     * @return bool
     */
    protected function _cmsActivePage($pageId)
    {
        if (!isset($this->_activeParentIds)) {
            $this->_activeParentIds = [];
            $pageLevels = PageBuilder::getData('pageLevels') ?: [];
            foreach ($pageLevels as $k => $parentPage) {
                if ($k > 0) { // ignore home page
                    $this->_activeParentIds[] = $parentPage->id;
                }
            }
        }
        return in_array($pageId, $this->_activeParentIds);
    }

    /**
     * @param string $view
     * @param bool $relativePath
     * @return View
     * @throws \Throwable
     */
    public function render($view, $relativePath = true)
    {
        $this->_preRenderFn ? call_user_func($this->_preRenderFn, $this) : null;
        $view = config('coaster-commerce.views') . $view;
        $renderedItems = '';
        usort($this->_items, function($a, $b) {
            return $a->position <=> $b->position;
        });
        $items = array_values($this->_items);
        foreach ($items as $key => $item) {
            /** @var FrontendItem $item */
            $renderedItems .= $item->render($view, $key + 1, count($items));
        }
        return view($view . 'menu', ['items' => $renderedItems]);
    }

}
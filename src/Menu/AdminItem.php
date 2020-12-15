<?php namespace CoasterCommerce\Core\Menu;

use Closure;
use CoasterCommerce\Core\Permissions\PermissionManager;
use Throwable;

class AdminItem
{

    /**
     * @var string
     */
    public $name;

    /**
     * @var string|Closure
     */
    public $url;

    /**
     * @var string
     */
    public $icon;

    /**
     * @var bool
     */
    public $active;

    /**
     * @var int
     */
    public $position;

    /**
     * @var static[]
     */
    public $subItems = [];

    /**
     * AdminItem constructor.
     * @param string $name
     * @param string $icon
     * @param int $position
     */
    public function __construct($name, $icon, $position = 10)
    {
        $this->name = $name;
        $this->icon = $icon;
        $this->position = $position;
    }

    /**
     * @param string $route
     * @param array $params
     * @return $this
     */
    public function setRoute($route, $params = [])
    {
        $this->url = function () use($route, $params) {
            return route($route, $params);
        };
        return $this;
    }

    /**
     * @param string $name
     * @param string $icon
     * @param string $url
     * @param int $position
     * @return $this
     */
    public function setSubItemUrl($name, $icon, $url, $position = 10)
    {
        $item = new AdminItem($name, $icon, $position);
        $item->setUrl($url);
        $this->subItems[] = $item;
        return $this;
    }

    /**
     * @param string $name
     * @param string $icon
     * @param string $route
     * @param array $params
     * @param int $position
     * @return $this
     */
    public function setSubItemRoute($name, $icon, $route, $params = [], $position = 10)
    {
        $item = new AdminItem($name, $icon, $position);
        $item->setRoute($route, $params);
        $this->subItems[] = $item;
        return $this;
    }

    /**
     * @param string $url
     * @return $this
     */
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    /**
     * @param bool $active
     */
    public function setActive($active = true)
    {
        $this->active = $active;
    }

    /**
     * @param string $key
     * @return string
     * @throws Throwable
     */
    public function render($key)
    {
        usort($this->subItems, function($a, $b) {
            return $a->position <=> $b->position;
        });
        if (!$this->_canAccess($this) || ($this->subItems && !$this->allowedSubItems())) {
            return '';
        }
        if (is_callable($this->url)) {
            $this->url = $this->url->__invoke();
        }
        return view('coaster-commerce::admin.menu.item', ['item' => $this, 'key' => $key])->render();
    }

    /**
     * @return static[]
     */
    public function allowedSubItems()
    {
        $allowedSubItems = [];
        foreach ($this->subItems as $subItem) {
            if ($this->_canAccess($subItem)) {
                $allowedSubItems[] = $subItem;
            }
        }
        return $allowedSubItems;
    }

    /**
     * @param static $item
     * @return bool
     */
    protected function _canAccess($item)
    {
        $permissionsManager = app(PermissionManager::class);
        if (is_callable($item->url)) {
            $urlFnStaticVars = (new \Reflectionfunction($item->url))->getStaticVariables();
            return !array_key_exists('route', $urlFnStaticVars) || $permissionsManager->hasPermission($urlFnStaticVars['route']);
        } else {
            return true;
        }
    }

}

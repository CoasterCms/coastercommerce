<?php namespace CoasterCommerce\Core\Menu;

use Closure;
use Throwable;

class FrontendItem
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
     * @var bool
     */
    public $active;

    /**
     * @var array
     */
    public $options;

    /**
     * @var int
     */
    public $position;

    /**
     * @var int
     */
    public $level;

    /**
     * @var FrontendItem
     */
    public $parentItem;

    /**
     * @var FrontendItem[]
     */
    public $subItems = [];

    /**
     * FrontendItem constructor.
     * @param FrontendItem $parentItem
     * @param string $name
     * @param string $url
     * @param bool $active
     * @param int $position
     * @param array $options
     */
    public function __construct($parentItem, $name, $url = null, $active = false, $position = 10, $options = [])
    {
        $this->parentItem = $parentItem;
        $this->level = $parentItem ? $parentItem->level + 1 : 1;
        if ($parentItem) {
            $parentItem->addSubItem($this);
        }
        $this->name = $name;
        $this->url = $url;
        $this->active = $active;
        $this->position = $position;
        $this->options = $options;
    }

    /**
     * @param int $position
     * @return $this
     */
    public function setPosition($position = 10)
    {
        $this->position = $position;
        return $this;
    }

    /**
     * @param string $route
     * @return $this
     */
    public function route($route)
    {
        $this->url = function () use($route) {
            return route($route);
        };
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
     * @param FrontendItem $item
     */
    public function addSubItem(FrontendItem $item)
    {
        $this->subItems[] = $item;
    }

    /**
     * @param string $view
     * @param int $i
     * @param int $total
     * @return string
     * @throws Throwable
     */
    public function render($view, $i, $total)
    {
        $viewData = $this->options + [
            'item' => $this,
            'is_first' => $i == 1,
            'is_last' => $i == $total,
            'total' => $total,
            'count' => $i,
            'level' => $this->level
        ];
        if (is_callable($this->url)) {
            $this->url = $this->url->__invoke();
        }
        if ($this->subItems) {
            usort($this->subItems, function($a, $b) {
                return $a->position <=> $b->position;
            });
            $items = '';
            $subItems = array_values($this->subItems);
            foreach ($subItems as $key => $subItem) {
                $items .= $subItem->render($view, $key + 1, count($subItems));
            }
            return view($view . 'submenu_' . $this->level, $viewData + ['items' => $items])->render();
        } else {
            return view($view . 'item', $viewData)->render();
        }
    }

}

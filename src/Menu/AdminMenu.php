<?php namespace CoasterCommerce\Core\Menu;

use Throwable;

class AdminMenu
{

    /**
     * @var AdminItem[]
     */
    protected $_items = [];

    /**
     * @param string $name
     * @param string $icon
     * @param string $url
     * @param int $position
     * @return AdminItem
     */
    public function setItemUrl($name, $icon, $url, $position = 10)
    {
        $item = new AdminItem($name, $icon, $position);
        $item->setUrl($url);
        $this->_items[] = $item;
        return $item;
    }

    /**
     * @param string $name
     * @param string $icon
     * @param string $route
     * @param array $params
     * @param int $position
     * @return AdminItem
     */
    public function setItemRoute($name, $icon, $route, $params = [], $position = 10)
    {
        $item = new AdminItem($name, $icon, $position);
        $item->setRoute($route, $params);
        $this->_items[] = $item;
        return $item;
    }

    /**
     *
     */
    public function clear()
    {
        $this->_items = [];
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
     * @return AdminItem
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
    public function setDefaults()
    {
        $this->setItemRoute('Dashboard', 'chart-line', 'coaster-commerce.admin.dashboard', [], 10);
        $this->setItemRoute('Orders', 'cart-arrow-down', 'coaster-commerce.admin.order.list', null, 20);
        $this->setItemUrl('Catalogue', 'box', null, 30)
            ->setSubItemRoute('Categories', '', 'coaster-commerce.admin.category.list', [], 20)
            ->setSubItemRoute('Products', '', 'coaster-commerce.admin.product.list', [], 10)
            ->setSubItemRoute('Product Attributes', '', 'coaster-commerce.admin.attribute.list', [], 30)
            ->setSubItemRoute('Redirects', '', 'coaster-commerce.admin.redirect.list', [], 40);
        $this->setItemUrl('Customers', 'user', null, 40)
            ->setSubItemRoute('Customers', '', 'coaster-commerce.admin.customer.list', [], 10)
            ->setSubItemRoute('Groups', '', 'coaster-commerce.admin.customer.group.list', [], 20)
            ->setSubItemRoute('Countries', '', 'coaster-commerce.admin.customer.countries', [], 30);
        $this->setItemRoute('Promotions', 'tags', 'coaster-commerce.admin.promotion.list', [], 50);
        $this->setItemUrl('Settings', 'cog', null, 100)
            ->setSubItemRoute('VAT Rules', '', 'coaster-commerce.admin.system.vat', [], 10)
            ->setSubItemRoute('Shipping Methods', '', 'coaster-commerce.admin.system.shipping', [], 20)
            ->setSubItemRoute('Payment Methods', '', 'coaster-commerce.admin.system.payment', [], 30)
            ->setSubItemRoute('Email Templates', '', 'coaster-commerce.admin.system.email', [], 40)
            ->setSubItemRoute('Store Details', '', 'coaster-commerce.admin.system.store', [], 50);
        $this->setItemUrl('Import', 'file-csv', null, 110)
            ->setSubItemRoute('Catalogue Products', '', 'coaster-commerce.admin.import.products', [], 10)
            ->setSubItemRoute('Catalogue Categories', '', 'coaster-commerce.admin.import.categories', [], 20)
            ->setSubItemRoute('Customers', '', 'coaster-commerce.admin.import.customers', [], 30);
        $this->setItemRoute('Permissions', 'key', 'coaster-commerce.admin.permission.list', null, 200);
        return $this;
    }

    /**
     * @return array
     * @throws Throwable
     */
    public function render()
    {
        usort($this->_items, function($a, $b) {
           return $a->position <=> $b->position;
        });
        return view('coaster-commerce::admin.menu.menu', ['items' => $this->_items])->render();
    }

}


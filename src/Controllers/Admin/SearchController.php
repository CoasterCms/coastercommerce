<?php
namespace CoasterCommerce\Core\Controllers\Admin;

use CoasterCommerce\Core\Menu\AdminMenu;
use CoasterCommerce\Core\Model\Product\SearchIndex;

class SearchController extends AbstractController
{

    /**
     *
     */
    protected function _init()
    {
        /** @var AdminMenu $adminMenu */
        $adminMenu = app('coaster-commerce.admin-menu');
        $adminMenu->getByName('Settings')->setActive();
    }

    /**
     *
     */
    public function reindex()
    {
        (new SearchIndex())->reindexAll();
        return 'complete';
    }

}

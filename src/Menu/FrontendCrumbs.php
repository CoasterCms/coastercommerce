<?php namespace CoasterCommerce\Core\Menu;

use Illuminate\View\View;

class FrontendCrumbs
{

    /**
     * @var FrontendCrumb
     */
    public $crumbs;

    /**
     * FrontendCrumbs constructor.
     * @param array $crumbs
     */
    public function __construct($crumbs)
    {
        $this->crumbs = $crumbs;
    }

    /**
     * @param string $view
     * @return View
     */
    public function render($view)
    {
        $view = config('coaster-commerce.views') . $view;
        $viewFactory = app('view');
        $renderedCrumbs = [];
        foreach ($this->crumbs as $crumb) {
            if ($viewFactory->exists($view . '.active_element') && $crumb->active) {
                $renderedCrumbs[] = $viewFactory->make($view . 'active_element', ['crumb' => $crumb]);
            } else {
                $renderedCrumbs[] = $viewFactory->make($view . 'link_element', ['crumb' => $crumb]);
            }
        }
        return $viewFactory->make($view . '.wrap', [
            'crumbs' => implode($viewFactory->make($view . '.separator'), $renderedCrumbs)
        ]);
    }

}

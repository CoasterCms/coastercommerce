<?php
namespace CoasterCommerce\Core\Controllers\Frontend;

use CoasterCommerce\Core\Events\FrontendInit;
use CoasterCommerce\Core\Menu\FrontendCrumb;
use CoasterCommerce\Core\MessageAlerts\FrontendAlert;
use Illuminate\Contracts\Session\Session;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\Routing\Redirector;
use Illuminate\View\Factory;
use CoasterCommerce\Core\Session\Cart;
use Illuminate\Contracts\View\View;

abstract class AbstractController extends Controller
{

    /**
     * @var Cart
     */
    protected $_cart;

    /**
     * @var Factory
     */
    protected $_view;

    /**
     * @var Redirector
     */
    protected $_urlRedirect;

    /**
     * @var Session
     */
    protected $_session;

    /**
     * AbstractController constructor.
     * @param Cart $cart
     * @param Factory $view
     * @param Redirector $urlRedirect
     * @param Session $session
     */
    public function __construct(
        Cart $cart,
        Factory $view,
        Redirector $urlRedirect,
        Session $session
    ) {
        $this->_cart = $cart;
        $this->_view = $view;
        $this->_urlRedirect = $urlRedirect;
        $this->_session = $session;
    }

    /**
     * @param string $view
     * @param array $viewData
     * @return View
     */
    protected function _view($view, $viewData = [])
    {
        return $this->_view->make(config('coaster-commerce.views') . 'templates.' . $view, $viewData);
    }

    /**
     * @param string $path
     * @param array|mixed $parameters
     * @param int $status
     * @param array $headers
     * @param bool $isRoute
     * @return RedirectResponse
     */
    protected function _redirect($path, $parameters = [], $status = 302, $headers = [], $isRoute = true)
    {
        if ($isRoute) {
            return $this->_urlRedirect->route('coaster-commerce.frontend.' . $path, $parameters, $status, $headers);
        } else {
            return $this->_urlRedirect->to($path, $status, $headers);
        }
    }

    /**
     * @param string $title
     * @param string $desc
     * @param string $keywords
     */
    protected function _setPageMeta($title, $desc = null, $keywords = null)
    {
        event(new FrontendInit(
            app('coaster-commerce.url-resolver')->setCustomPage([new FrontendCrumb($title, null)], $title, $desc, $keywords)
        ));
    }

    /**
     * @return FrontendAlert
     */
    protected function _messageAlerts()
    {
        return app(FrontendAlert::class);
    }

    /**
     * Passes alerts straight to view (useful if not redirecting)
     * @param array $newAlerts
     */
    protected function _addAlerts($newAlerts)
    {
        $this->_messageAlerts()->addAlerts($newAlerts);
    }

    /**
     * Adds alerts on next page load (useful if redirecting)
     * @param string $class
     * @param string $content
     */
    protected function _flashAlert($class, $content)
    {
        $this->_messageAlerts()->flashAlert($class, $content);
    }

}

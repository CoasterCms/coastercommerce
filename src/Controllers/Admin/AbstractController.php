<?php
namespace CoasterCommerce\Core\Controllers\Admin;

use Illuminate\Contracts\Session\Session;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\Routing\Redirector;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Illuminate\View\Factory;

abstract class AbstractController extends Controller
{

    const SESSION_ALERTS_KEY = 'coaster-commerce.alerts';

    /**
     * @var string
     */
    protected $_title;

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
     * @var array
     */
    protected $_alerts;

    /**
     * AbstractController constructor.
     * @param Factory $view
     * @param Redirector $urlRedirect
     * @param Session $session
     */
    public function __construct(
        Factory $view,
        Redirector $urlRedirect,
        Session $session
    ) {
        $this->_title = 'Coaster Ecommerce';
        $this->_view = $view;
        $this->_urlRedirect = $urlRedirect;
        $this->_session = $session;
        $this->_init();

        // load flashed alerts
        $this->_alerts = [];
        $this->middleware(function ($request, $next) {
            $alerts = $this->_session->get(static::SESSION_ALERTS_KEY, []);
            foreach ($alerts as $alertClass => $alertArray) {
                foreach ($alertArray as $alert) {
                    $this->_addAlert($alertClass, $alert);
                }
            }
            $this->_session->put(static::SESSION_ALERTS_KEY, []);
            return $next($request);
        });
    }

    /**
     * @param string $title
     * @param bool $append
     */
    protected function _setTitle($title, $append = true)
    {
        if ($append) {
            $this->_title = $title . ' | ' . $this->_title;
        } else {
            $this->_title = $title;
        }
    }

    /**
     * @param string $view
     * @param array $viewData
     * @return View
     */
    protected function _view($view, $viewData = [])
    {
        $renderedContent = $this->_view->make('coaster-commerce::admin.' . $view, $viewData);
        return $this->_view->make('coaster-commerce::admin.layout', [
            'title' => $this->_title,
            'content' => $renderedContent,
            'alerts' => $this->_alerts,
            'ccRoutes' => $this->_ccRoutes()
        ]);
    }

    /**
     * @param string $path
     * @param array|mixed $parameters
     * @param int $status
     * @param array $headers
     * @return RedirectResponse
     */
    protected function _redirectRoute($path, $parameters = [], $status = 302, $headers = [])
    {
        return $this->_urlRedirect->route('coaster-commerce.admin.' . $path, $parameters, $status, $headers);
    }

    /**
     * @param string $class
     * @param string $content
     */
    protected function _addAlert($class, $content)
    {
        $this->_alerts[$class][] = $content;
    }

    /**
     * @param string $class
     * @param string $content
     */
    protected function _flashAlert($class, $content)
    {
        $alerts = $this->_session->get(static::SESSION_ALERTS_KEY, []);
        $alerts[$class][] = $content;
        $this->_session->put(static::SESSION_ALERTS_KEY, $alerts);
    }

    /**
     * @return View
     */
    protected function _notFoundView()
    {
        return response($this->_view('notfound'), 404);
    }

    /**
     * @return string
     */
    protected function _ccRoutes()
    {
        $ccRoutes = [];

        /** @var Router $declaredRoutes */
        $declaredRoutes = app('router');

        foreach($declaredRoutes->getRoutes() as $route) {
            /** @var Route $route */
            $action = $route->getAction();
            if (!empty($action['as']) &&
                (strpos($action['as'], 'coaster-commerce.admin') === 0 || strpos($action['as'], 'coaster-commerce.api') === 0)) {
                $ccRoutes[$action['as']] = $route->uri;
            }
        }

        return json_encode($ccRoutes);
    }

    /**
     *
     */
    protected function _init()
    {
    }

}

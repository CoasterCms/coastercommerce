<?php namespace CoasterCommerce\Core\MessageAlerts;

use Illuminate\Contracts\Session\Session;
use Illuminate\View\Factory;

class FrontendAlert
{

    const SESSION_ALERTS_KEY = 'coaster-commerce.frontend-messages';

    /**
     * @var Session
     */
    protected $_session;

    /**
     * @var Factory
     */
    protected $_view;

    /**
     * MessageAlerts constructor.
     * @param Session $session
     * @param Factory $view
     */
    public function __construct(Session $session, Factory $view)
    {
        $this->_session = $session;
        $this->_view = $view;
    }

    /**
     * Passes alerts straight to view (useful if not redirecting)
     * @param array $newAlerts
     */
    public function addAlerts($newAlerts)
    {
        $messageAlerts = $this->_view->shared('ccMessageAlerts', []);
        foreach ($newAlerts as $alertKey => $alertMessages) {
            foreach ($alertMessages as $alertMessage) {
                $messageAlerts[$alertKey][] = $alertMessage;
            }
        }
        $this->_view->share('ccMessageAlerts', $messageAlerts);
    }

    /**
     * @param string $class
     * @param string $content
     */
    public function addAlert($class, $content)
    {
        $this->addAlerts([$class => [$content]]);
    }

    /**
     * Passes alerts straight to view (useful if not redirecting)
     * @param array $newAlerts
     */
    public function flashAlerts($newAlerts)
    {
        $messageAlerts = $this->_session->get(static::SESSION_ALERTS_KEY, []);
        foreach ($newAlerts as $alertKey => $alertMessages) {
            foreach ($alertMessages as $alertMessage) {
                $messageAlerts[$alertKey][] = $alertMessage;
            }
        }
        $this->_session->put(static::SESSION_ALERTS_KEY, $messageAlerts);
    }

    /**
     * Adds alerts on next page load (useful if redirecting)
     * @param string $class
     * @param string $content
     */
    public function flashAlert($class, $content)
    {
        $this->flashAlerts([$class => [$content]]);
    }

    /**
     * Clears flashed alerts, pushes them to view
     */
    public function flushFlashedAlerts()
    {
        $alerts = $this->_session->get(static::SESSION_ALERTS_KEY, []);
        $this->addAlerts($alerts);
        $this->_session->put(static::SESSION_ALERTS_KEY, []);
    }

}


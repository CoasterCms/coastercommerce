<?php namespace CoasterCommerce\Core\Middleware;

use Closure;
use CoasterCommerce\Core\MessageAlerts\FrontendAlert;
use Illuminate\Http\Request;

class MessageAlerts
{

    /**
     * @var FrontendAlert
     */
    protected $_alerts;

    /**
     * MessageAlerts constructor.
     * @param FrontendAlert $alerts
     */
    public function __construct(FrontendAlert $alerts)
    {
        $this->_alerts = $alerts;
    }

    /**
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $this->_alerts->flushFlashedAlerts();
        return $next($request);
    }

}


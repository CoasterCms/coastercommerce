<?php namespace CoasterCommerce\Core\Middleware;

use Closure;
use Illuminate\Auth\AuthManager;
use Illuminate\Http\Request;

abstract class CoasterCms
{

    /**
     * @var AuthManager
     */
    protected $_auth;

    /**
     * Auth constructor.
     * @param AuthManager $auth
     */
    public function __construct(AuthManager $auth)
    {
        $this->_auth = $auth;
    }

    /**
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function adminHook(Request $request, Closure $next)
    {
        $defaultGuard = $this->_auth->guard();
        if (method_exists($defaultGuard, 'admin')) {
            if ($defaultGuard->admin()) { // $defaultGuard is probably CoasterGuard
                return $next($request);
            }
        }
        return false;
    }

}


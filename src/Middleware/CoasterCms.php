<?php namespace CoasterCommerce\Core\Middleware;

use Closure;
use CoasterCommerce\Core\Permissions\PermissionManager;
use Illuminate\Auth\AuthManager;
use Illuminate\Http\Request;

abstract class CoasterCms
{

    /**
     * @var AuthManager
     */
    protected $_auth;

    /**
     * @var PermissionManager
     */
    protected $_permissionManager;

    /**
     * Auth constructor.
     * @param AuthManager $auth
     */
    public function __construct(AuthManager $auth, PermissionManager $permissionManager)
    {
        $this->_auth = $auth;
        $this->_permissionManager = $permissionManager;
    }

    /**
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function adminHook(Request $request, Closure $next, $redirectToDash = false)
    {
        $defaultGuard = $this->_auth->guard();
        if (method_exists($defaultGuard, 'admin')) {
            if ($defaultGuard->admin()) { // $defaultGuard is probably CoasterGuard
                if ($this->_permissionManager->hasPermission($request->route()->getName(), $defaultGuard->user())) {
                    return $next($request);
                } elseif ($redirectToDash) {
                    return redirect()->route('coaster-commerce.admin.dashboard');
                }
            }
        }
        return false;
    }

}


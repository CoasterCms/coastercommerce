<?php namespace CoasterCommerce\Core\Middleware;

use Closure;
use Illuminate\Http\Request;

class Api extends CoasterCms
{

    /**
     * @param Request $request
     * @param Closure $next
     * @param string $role
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $role)
    {
        if ($role == 'admin') {
            return $this->adminHook($request, $next) ?: response()->json('Unauthorised', 401);
        } else {
            return $next($request);
        }
    }

}

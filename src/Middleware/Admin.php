<?php namespace CoasterCommerce\Core\Middleware;

use Closure;
use Illuminate\Http\Request;

class Admin extends CoasterCms
{

    /**
     * @param Request $request
     * @param  Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        return $this->adminHook($request, $next, true) ?:
            redirect()->route('coaster.admin.login')->withCookie(cookie('login_path', $request->getRequestUri()));
    }

}


<?php namespace CoasterCommerce\Core\Middleware;

use Closure;
use CoasterCommerce\Core\CatalogueUrls\UrlResolver;
use Illuminate\Http\Request;

class CatalogueRoute
{

    protected $_urlResolver;

    public function __construct(UrlResolver $urlResolver)
    {
        $this->_urlResolver = $urlResolver;
    }

    /**
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if ($this->_urlResolver->isCommerceUrl($request)) {
            return $this->_urlResolver->generateResponse();
        } else {
            return $next($request);
        }
    }

}

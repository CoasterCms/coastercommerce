<?php namespace CoasterCommerce\Core\Events;

use CoasterCommerce\Core\CatalogueUrls\UrlResolver;

class FrontendInit
{

    /**
     * @var array
     */
    public $urlResolver;

    /**
     * FrontendInit constructor.
     * @param UrlResolver $urlResolver
     */
    public function __construct(UrlResolver $urlResolver)
    {
        $this->urlResolver = $urlResolver;
    }

}


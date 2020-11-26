<?php namespace CoasterCommerce\Core\Menu;

class FrontendCrumb
{

    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $url;

    /**
     * @var bool
     */
    public $active;

    /**
     * FrontendCrumb constructor.
     * @param string $name
     * @param string $url
     * @param bool $active
     */
    public function __construct($name, $url, $active = false)
    {
        $this->name = $name;
        $this->url = $url;
        $this->active = $active;
    }

}

<?php namespace CoasterCommerce\Core\Listeners;

use CoasterCms\Events\Admin\LoadResponse;
use Illuminate\Auth\AuthManager;

class AdminCoasterResponse
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
     * @param LoadResponse $event
     */
    public function handle(LoadResponse $event)
    {
        if (array_key_exists('system_menu', $event->layoutData)) {
            $defaultGuard = $this->_auth->guard();
            if (method_exists($defaultGuard, 'admin')) {
                if ($defaultGuard->admin()) { // $defaultGuard is probably CoasterGuard
                    $event->layoutData['system_menu'] = str_replace(
                        'Frontend',
                        'Frontend</a></li><li><a href="' . config('coaster-commerce.url.admin') . '"><i class="fa fa-shopping-cart"></i> Ecomm',
                        $event->layoutData['system_menu']
                    );
                }
            }
        }
    }

}


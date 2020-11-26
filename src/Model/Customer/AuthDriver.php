<?php

namespace CoasterCommerce\Core\Model\Customer;

use CoasterCommerce\Core\Model\Customer\Auth\Magento;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable as UserContract;

class AuthDriver extends EloquentUserProvider
{

    /**
     * @param UserContract $user
     * @param array $credentials
     * @return bool
     */
    public function validateCredentials(UserContract $user, array $credentials)
    {
        return (new Magento($credentials['password'], $user->getAuthPassword()))->isValid() ?:  // check credentials using magento hash algorithm for old passwords
            parent::validateCredentials($user, $credentials); // normal credentials check for password created in current system
    }

}

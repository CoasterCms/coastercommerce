<?php namespace CoasterCommerce\Core\Listeners;

use CoasterCommerce\Core\Events\ValidateOrderAddress;
use Illuminate\Validation\ValidationException;

class ValidateOrderAddressRules
{

    /**
     * @param ValidateOrderAddress $event
     * @throws ValidationException
     */
    public function handle(ValidateOrderAddress $event)
    {
        validator($event->request->all(), $event->validationRules)->validate();
    }

}


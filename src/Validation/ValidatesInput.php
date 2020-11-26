<?php

namespace CoasterCommerce\Core\Validation;

use Illuminate\Foundation\Validation\ValidatesRequests;

trait ValidatesInput
{
    use ValidatesRequests;

    /**
     * Validate the given request with the given rules.
     *
     * @param  array  $inputData
     * @param  array  $rules
     * @param  array  $messages
     * @param  array  $customAttributes
     * @return array
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function validate($inputData, array $rules,
                             array $messages = [], array $customAttributes = [])
    {
        return $this->getValidationFactory()->make(
            $inputData, $rules, $messages, $customAttributes
        )->validate();
    }

}

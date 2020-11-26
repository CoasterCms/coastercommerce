<?php namespace CoasterCommerce\Core\Events;

use CoasterCommerce\Core\Model\EmailSetting;

class AdminEmailSave
{

    /**
     * @var EmailSetting
     */
    public $email;

    /**
     * @var array
     */
    public $inputData;

    /**
     * AdminEmailSave constructor.
     * @param EmailSetting $email
     * @param array $inputData
     */
    public function __construct(EmailSetting $email, array $inputData)
    {
        $this->email = $email;
        $this->inputData = $inputData;
    }

}


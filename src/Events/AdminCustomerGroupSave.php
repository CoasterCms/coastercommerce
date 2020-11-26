<?php namespace CoasterCommerce\Core\Events;

use CoasterCommerce\Core\Model\Customer\Group;

class AdminCustomerGroupSave
{

    /**
     * @var Group
     */
    public $group;

    /**
     * @var array
     */
    public $inputData;

    /**
     * AdminCustomerGroupSave constructor.
     * @param Group  $group
     * @param array $inputData
     */
    public function __construct(Group $group, array $inputData)
    {
        $this->group = $group;
        $this->inputData = $inputData;
    }

}


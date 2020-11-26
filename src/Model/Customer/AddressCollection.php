<?php

namespace CoasterCommerce\Core\Model\Customer;

use Illuminate\Database\Eloquent\Collection;

class AddressCollection extends Collection
{

    /**
     * @return array
     */
    public function selectOptions()
    {
        $options = [];
        foreach ($this->items as $address) {
            /** @var Address $address */
            $options[$address->id] = implode(', ', array_filter([
                trim($address->first_name . ' ' . $address->last_name),
                $address->company,
                $address->address_line_1,
                $address->address_line_2,
                $address->town,
                $address->county,
                $address->postcode,
                $address->country()
            ]));
        }
        return $options;
    }

}

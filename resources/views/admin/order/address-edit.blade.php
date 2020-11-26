<?php
/**
 * @var \Collective\Html\FormBuilder $formBuilder
 * @var \CoasterCommerce\Core\Model\Order\Address $address
 */
use CoasterCommerce\Core\Renderer\Admin\Attribute;
use CoasterCommerce\Core\Model\Setting;
?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">

                <div class="row">
                    <h1 class="card-title col-sm-7 mb-5">
                        Edit {{ ucwords($address->type) }} Address
                    </h1>
                    <div class="col-sm-5 mb-5 text-right">&nbsp;
                        <a href="javascript:$('#addressForm').submit()" class="btn btn-success mb-2">
                            <i class="fa fa-save"></i> &nbsp; Save
                        </a> &nbsp;
                        <a href="{{ route('coaster-commerce.admin.order.view', ['id' => $address->order_id]) }}" class="btn btn-success mb-2">
                            <i class="fa fa-arrow-circle-left"></i> &nbsp; Return to order
                        </a>
                    </div>
                </div>

                {!! $formBuilder->open(['url' => route('coaster-commerce.admin.order.address.update', ['id' => $address->order_id, 'type' => $address->type]), 'id' => 'addressForm']) !!}

                    {!! (new Attribute('first_name', 'text', 'First Name'))->key('address')->renderInput($address->first_name) !!}
                    {!! (new Attribute('last_name', 'text', 'Last Name'))->key('address')->renderInput($address->last_name) !!}
                    {!! (new Attribute('company', 'text', 'Company'))->key('address')->renderInput($address->company) !!}
                    {!! (new Attribute('address_line_1', 'text', 'Address Line 1'))->key('address')->renderInput($address->address_line_1) !!}
                    {!! (new Attribute('address_line_2', 'text', 'Address Line 2'))->key('address')->renderInput($address->address_line_2) !!}
                    {!! (new Attribute('town', 'text', 'Town'))->key('address')->renderInput($address->town) !!}
                    {!! (new Attribute('county', 'text', 'County'))->key('address')->renderInput($address->county) !!}
                    {!! (new Attribute('postcode', 'text', 'Postcode'))->key('address')->renderInput($address->postcode) !!}
                    {!! (new Attribute('country_iso3', 'select', 'Country'))->key('address')->renderInput($address->country_iso3 ?: Setting::getValue('country_default'), ['options' => $countries]) !!}
                    {!! (new Attribute('email', 'text', 'Email'))->key('address')->renderInput($address->email) !!}
                    {!! (new Attribute('phone', 'text', 'Phone'))->key('address')->renderInput($address->phone) !!}

                    <button type="submit" class="btn btn-success mt-2">Update Address</button>

                {!! $formBuilder->close() !!}

            </div>
        </div>
    </div>
</div>


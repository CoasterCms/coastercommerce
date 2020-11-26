<?php
/**
 * @var CoasterCommerce\Core\Model\Customer $customer
 */
use CoasterCommerce\Core\Renderer\Admin\Attribute;
?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">

                <form action="{{ route('coaster-commerce.admin.customer.save', ['id' => $customer->exists ? $customer->id : 0]) }}" method="post">

                    {!! csrf_field() !!}

                    <div class="row">

                        <h1 class="card-title col-sm-7 mb-5">
                            @if ($customer->exists)
                                Edit {{ $customer->email }}
                            @else
                                New Customer
                            @endif
                        </h1>

                        <div class="col-sm-5 mb-5 text-right">
                            @if ($customer->exists)
                                <a href="{{ route('coaster-commerce.admin.customer.delete', ['id' => $customer->id]) }}" class="btn btn-danger confirm mb-2" data-confirm="you wish to delete this customer">
                                    <i class="fas fa-trash-alt"></i> &nbsp; Delete
                                </a> &nbsp;
                            @endif
                            <button name="saveAction" value="continue" class="btn btn-success mb-2">
                                <i class="fas fa-save"></i> &nbsp; Save
                            </button> &nbsp;
                            <button name="saveAction" value="return" class="btn btn-success mb-2">
                                <i class="fas fa-arrow-circle-left"></i> &nbsp; Save & return to customer list
                            </button>
                        </div>

                    </div>

                    <div class="row">

                        <div class="col-md-2 mb-3">
                            <ul class="nav nav-pills flex-column" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" id="general-tab" data-toggle="tab" href="#general" role="tab" aria-controls="home" aria-selected="true">
                                        General
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="address-tab" data-toggle="tab" href="#addresses" role="tab" aria-controls="home" aria-selected="true">
                                        Addresses
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="other-tab" data-toggle="tab" href="#other" role="tab" aria-controls="home" aria-selected="true">
                                        Other Info
                                    </a>
                                </li>
                            </ul>
                        </div>

                        <div class="col-md-10">
                            <div class="tab-content">

                                <div class="tab-pane fade show active" id="general" role="tabpanel" aria-labelledby="general-tab">
                                    {!! (new Attribute('email', 'text', 'Email'))->renderInput($customer->email) !!}
                                    {!! (new Attribute('group_id', 'select', 'Group'))->renderInput($customer->group_id ?: \CoasterCommerce\Core\Model\Setting::getValue('customer_default_group'), ['options' => $groups]) !!}
                                    {!! (new Attribute('password', 'password', 'Password'))->renderInput(null, ['note' => 'leave blank to keep current customer password']) !!}
                                </div>

                                <div class="tab-pane fade" id="addresses" role="tabpanel" aria-labelledby="meta-tab">

                                    {!! $formBuilder->select('addresses', $customer->addresses->selectOptions() + [0 => '-- New Address --'], null, ['class' => 'form-control mb-5', 'id' => 'addressSelected']) !!}

                                    @php
                                        $newAddress = new \CoasterCommerce\Core\Model\Customer\Address();
                                        $newAddress->id = 0;
                                        $addresses = $customer->addresses->push($newAddress);
                                    @endphp
                                    @foreach($addresses as $address)
                                        <div class="customer-address" data-id="{{ $address->id }}">
                                        {!! (new Attribute('first_name', 'text', 'First Name'))->key('address.'.$address->id)->renderInput($address->first_name) !!}
                                        {!! (new Attribute('last_name', 'text', 'Last Name'))->key('address.'.$address->id)->renderInput($address->last_name) !!}
                                        {!! (new Attribute('company', 'text', 'Company'))->key('address.'.$address->id)->renderInput($address->company) !!}
                                        {!! (new Attribute('address_line_1', 'text', 'Address Line 1'))->key('address.'.$address->id)->renderInput($address->address_line_1) !!}
                                        {!! (new Attribute('address_line_2', 'text', 'Address Line 2'))->key('address.'.$address->id)->renderInput($address->address_line_2) !!}
                                        {!! (new Attribute('town', 'text', 'Town'))->key('address.'.$address->id)->renderInput($address->town) !!}
                                        {!! (new Attribute('county', 'text', 'County'))->key('address.'.$address->id)->renderInput($address->county) !!}
                                        {!! (new Attribute('postcode', 'text', 'Postcode'))->key('address.'.$address->id)->renderInput($address->postcode) !!}
                                        {!! (new Attribute('country_iso3', 'select', 'Country'))->key('address.'.$address->id)->renderInput($address->country_iso3 ?: \CoasterCommerce\Core\Model\Setting::getValue('country_default'), ['options' => $countries]) !!}
                                        {!! (new Attribute('email', 'text', 'Email'))->key('address.'.$address->id)->renderInput($address->email) !!}
                                        {!! (new Attribute('phone', 'text', 'Phone'))->key('address.'.$address->id)->renderInput($address->phone) !!}
                                        {!! (new Attribute('default_billing', 'switch', 'Default Billing'))->key('address.'.$address->id)->renderInput($address->default_billing) !!}
                                        {!! (new Attribute('default_shipping', 'switch', 'Default Shipping'))->key('address.'.$address->id)->renderInput($address->default_shipping) !!}
                                        <div class="row mt-4">
                                            <div class="offset-sm-3 col-sm-9">
                                                <a href="javascript:void(0)" class="text-danger delete">Delete Address</a>
                                            </div>
                                        </div>
                                        </div>
                                    @endforeach

                                </div>

                                <div class="tab-pane fade" id="other" role="tabpanel" aria-labelledby="meta-tab">
                                    <table class="table table-hover other-info">
                                        <thead>
                                            <tr>
                                                <th>Field</th>
                                                <th>Value</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                            if ($errors && $metaKeys = old('meta.key')) {
                                                // reload unsaved input data on errors
                                                $metas = [];
                                                $metaValues = old('meta.value', []);
                                                foreach ($metaKeys as $i => $key) {
                                                    if (is_null($key)) continue;
                                                    $meta = new \CoasterCommerce\Core\Model\Customer\Meta();
                                                    $meta->key = $key;
                                                    $meta->value = $metaValues[$i];
                                                    $metas[] = $meta;
                                                }
                                            } else {
                                                $metas = $customer->meta;
                                            }
                                            @endphp
                                            <tr class="d-none" id="templateOtherRow">
                                                <td>
                                                    {{ $formBuilder->text('meta[key][]', null, ['class' => 'form-borderless']) }}
                                                </td>
                                                <td>
                                                    {{ $formBuilder->text('meta[value][]', null, ['class' => 'form-borderless']) }}
                                                </td>
                                                <td>
                                                    <a href="javascript:void(0)"><i class="fa fa-trash"></i></a>
                                                </td>
                                            </tr>
                                            @foreach($metas as $metaData)
                                                <tr>
                                                    <td>
                                                        {{ $formBuilder->text('meta[key][]', $metaData->key, ['class' => 'form-borderless']) }}
                                                    </td>
                                                    <td>
                                                        {{ $formBuilder->text('meta[value][]', $metaData->value, ['class' => 'form-borderless']) }}
                                                    </td>
                                                    <td>
                                                        <a href="javascript:void(0)"><i class="fa fa-trash"></i></a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                    <button class="btn btn-info" type="button" id="addOtherInfo">Add Customer Info</button>
                                </div>

                            </div>
                        </div>

                    </div>

                </form>

            </div>
        </div>
    </div>
</div>

@section('scripts')
    <script>
        $(document).ready(function() {

            let firstError = $('.is-invalid').first();
            if (firstError) {
                $('#' + firstError.closest('.tab-pane').attr('id') + '-tab').click();
            }

            $('.other-info').on('click', '.fa-trash', function (e) {
               $(e.target).closest('tr').remove();
            });

            $('#addOtherInfo').click(function () {
                $('.other-info tbody').append($('#templateOtherRow').prop('outerHTML').replace('d-none', ''));
            });

            $('#addressSelected').change(function () {
                $('.customer-address').css('display', 'none');
                $('.customer-address[data-id='+$(this).val()+']').css('display', 'block');
            }).trigger('change');

            $('#addresses').on('click', '.delete', function () {
                var address = $(this).closest('.customer-address');
                address.remove();
                $('#addressSelected').find('[value="' + address.data('id') + '"]').remove();
                setTimeout(function () {
                    $('#addressSelected').trigger('change');
                }, 100);
            });

            $('input[id$="_default_billing"]').change(function () {
                if ($(this).prop('checked')) {
                    var checkedAddressId = $(this).attr('id');
                    $('input[id$="_default_billing"]').each(function () {
                        if (checkedAddressId !== $(this).attr('id') && $(this).prop('checked')) {
                            $(this).prop('checked', 0).trigger('change');
                        }
                    });
                }
            });

            $('input[id$="_default_shipping"]').change(function () {
                if ($(this).prop('checked')) {
                    var checkedAddressId = $(this).attr('id');
                    $('input[id$="_default_shipping"]').each(function () {
                        if (checkedAddressId !== $(this).attr('id') && $(this).prop('checked')) {
                            $(this).prop('checked', 0).trigger('change');
                        }
                    });
                }
            });

        });
    </script>
@append
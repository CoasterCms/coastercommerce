<?php
/**
 * @var \Collective\Html\FormBuilder $formBuilder
 */
use CoasterCommerce\Core\Renderer\Admin\Attribute;
use CoasterCommerce\Core\Model\Setting;
?>

<div class="row">
    <div class="col-sm-4">
        <div class="card">
            <div class="card-body">
                <h1 class="card-title">Product VAT Classes</h1>
                {!! $formBuilder->open(['route' => 'coaster-commerce.admin.system.vat.settings.save']) !!}
                <div class="form-group">
                    <label for="exampleFormControlSelect1">Default Class</label>
                    <div class="input-group mb-3">
                        {!! $formBuilder->select('vat_tax_class', $taxClasses->pluck('name', 'id'), Setting::getValue('vat_tax_class'), ['class' => 'form-control']) !!}
                        <div class="input-group-append">
                            {!! $formBuilder->submit('Save', ['class' => 'btn btn-success']) !!}
                        </div>
                    </div>
                </div>
                {!! $formBuilder->close() !!}
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Tax Class</th>
                            <th></th>
                        </tr>
                    </thead>
                    @foreach($taxClasses as $taxClass)
                        <tbody>
                            <tr>
                                <td>{{ $taxClass->id }}</td>
                                <td>{{ $taxClass->name }}</td>
                                <td style="width:70px;">
                                    <a href="{{ route('coaster-commerce.admin.system.vat.class.edit', ['id' => $taxClass->id]) }}"><span class="fa fa-edit"></span></a> &nbsp;
                                    <a href="{{ route('coaster-commerce.admin.system.vat.class.delete', ['id' => $taxClass->id]) }}"><span class="fa fa-trash"></span></a>
                                </td>
                            </tr>
                        </tbody>
                    @endforeach
                </table>
                <a href="{{ route('coaster-commerce.admin.system.vat.class.add') }}" class="btn btn-success">Add New</a>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card">
            <div class="card-body">
                <h1 class="card-title">VAT Zones</h1>
                {!! $formBuilder->open(['route' => 'coaster-commerce.admin.system.vat.settings.save']) !!}
                <div class="form-group">
                    <label for="exampleFormControlSelect1">Default Zone</label>
                    <div class="input-group mb-3">
                        {!! $formBuilder->select('vat_tax_zone', $taxZones->pluck('name', 'id'), Setting::getValue('vat_tax_zone'), ['class' => 'form-control']) !!}
                        <div class="input-group-append">
                            {!! $formBuilder->submit('Save', ['class' => 'btn btn-success']) !!}
                        </div>
                    </div>
                </div>
                {!! $formBuilder->close() !!}
                <table class="table table-bordered">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Tax Zone</th>
                        <th></th>
                    </tr>
                    </thead>
                    @foreach($taxZones as $taxZone)
                        <tbody>
                        <tr>
                            <td>{{ $taxZone->id }}</td>
                            <td>{{ $taxZone->name }}</td>
                            <td style="width:70px;">
                                <a href="{{ route('coaster-commerce.admin.system.vat.zone.edit', ['id' => $taxZone->id]) }}"><span class="fa fa-edit"></span></a> &nbsp;
                                <a href="{{ route('coaster-commerce.admin.system.vat.zone.delete', ['id' => $taxZone->id]) }}"><span class="fa fa-trash"></span></a>
                            </td>
                        </tr>
                        </tbody>
                    @endforeach
                </table>
                <a href="{{ route('coaster-commerce.admin.system.vat.zone.add') }}" class="btn btn-success">Add New</a>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card">
            <div class="card-body">
                {!! $formBuilder->open(['route' => 'coaster-commerce.admin.system.vat.settings.save']) !!}
                <h3>Calculation</h3>
                @php
                    $options = ['inc' => 'Include VAT', 'ex' => 'Exclude VAT'];
                    $vatSettings = [
                        'vat_catalogue_price' => 'Catalogue prices',
                        'vat_shipping_price' => 'Shipping prices',
                    ];
                @endphp
                @foreach($vatSettings as $settingKey => $settingLabel)
                <div class="form-group row">
                    <label class="col-sm-6">{{ $settingLabel }}</label>
                    {!! $formBuilder->select($settingKey, $options, Setting::getValue($settingKey), ['class' => 'form-control col-sm-6']) !!}
                </div>
                @endforeach
                @php
                    $options = ['inc' => 'VAT Inclusive', 'ex' => 'VAT Exclusive'];
                    $vatSettings = [
                        'vat_catalogue_discount_calculation' => 'Apply catalogue discount to',
                        'vat_cart_discount_calculation' => 'Apply cart discount to',
                    ];
                @endphp
                @foreach($vatSettings as $settingKey => $settingLabel)
                    <div class="form-group row">
                        <label class="col-sm-6">{{ $settingLabel }}</label>
                        {!! $formBuilder->select($settingKey, $options, Setting::getValue($settingKey), ['class' => 'form-control col-sm-6']) !!}
                    </div>
                @endforeach
                <div class="form-group row">
                    <label class="col-sm-6">VAT calculated on</label>
                    {!! $formBuilder->select('vat_calculate_on', ['order' => 'Subtotal (ignores product tax class)', 'item' => 'Line Item', 'unit' => 'Unit'], Setting::getValue('vat_calculate_on'), ['class' => 'form-control col-sm-6']) !!}
                </div>
                <h3>Display</h3>
                @php
                    $options = ['inc' => 'Including VAT', 'ex' => 'Excluding VAT'];
                    $vatSettings = ['vat_catalogue_display' => 'Catalogue prices'];
                @endphp
                @foreach($vatSettings as $settingKey => $settingLabel)
                    <div class="form-group row">
                        <label class="col-sm-6">{{ $settingLabel }}</label>
                        {!! $formBuilder->select($settingKey, $options, Setting::getValue($settingKey), ['class' => 'form-control col-sm-6']) !!}
                    </div>
                @endforeach
                {!! $formBuilder->submit('Save Settings', ['class' => 'btn btn-success']) !!}
                {!! $formBuilder->close() !!}
            </div>
        </div>
    </div>
    <div class="col-sm-12">
        <div class="card">
            <div class="card-body">
                <h1 class="card-title">VAT Rules</h1>
                <p>The rate for the matching rule (biggest priority if multiple matches) will be used for price calculation and display.</p>
                <table class="table table-bordered">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Tax Class</th>
                        <th>Tax Zone</th>
                        <th>Customer Group</th>
                        <th>Priority</th>
                        <th>Rate</th>
                        <th></th>
                    </tr>
                    </thead>
                    @foreach($taxRules as $taxRule)
                        <tbody>
                        <tr>
                            <td>{{ $taxRule->id }}</td>
                            <td>{{ $taxRule->name }}</td>
                            <td>{{ $taxRule->taxClass->name }}</td>
                            <td>{{ $taxRule->taxZone->name }}</td>
                            <td>{{ $taxRule->customerGroupName() }}</td>
                            <td>{{ $taxRule->priority }}</td>
                            <td>{{ $taxRule->percentage }}</td>
                            <td style="width:70px;">
                                <a href="{{ route('coaster-commerce.admin.system.vat.rule.edit', ['id' => $taxRule->id]) }}"><span class="fa fa-edit"></span></a> &nbsp;
                                <a href="{{ route('coaster-commerce.admin.system.vat.rule.delete', ['id' => $taxRule->id]) }}"><span class="fa fa-trash"></span></a>
                            </td>
                        </tr>
                        </tbody>
                    @endforeach
                </table>
                <a href="{{ route('coaster-commerce.admin.system.vat.rule.add') }}" class="btn btn-success">Add New</a>
            </div>
        </div>
    </div>
</div>

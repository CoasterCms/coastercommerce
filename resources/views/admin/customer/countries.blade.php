<?php
/**
 * @var array $countries
 * @var array $allCountries
 */
use CoasterCommerce\Core\Renderer\Admin\Attribute;
use CoasterCommerce\Core\Model\Setting;
?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">

                <h1 class="card-title mb-5">Countries</h1>

                <form action="{{ route('coaster-commerce.admin.customer.countries.save') }}" method="post">

                    {!! csrf_field() !!}

                    <p>Set allowed countries for customer or guest addresses.</p>

                    {!! (new Attribute('rule', 'select', 'Rule'))->key()->renderInput(Setting::getValue('country_rule'), ['options' => ['specific' => 'Allow only selected', 'except' => 'Allow all except selected']]) !!}
                    {!! (new Attribute('countries', 'select-multiple', 'Selected Countries'))->key()->renderInput($countries, ['options' => $allCountries]) !!}

                    <div class="row">
                        <div class="col-sm-9 offset-sm-3">
                            <a href="javascript:void(0)" id="selectAll" class="small">select all</a> / <a href="javascript:void(0)" id="clearAll" class="small">clear</a>
                        </div>
                    </div>

                    {!! (new Attribute('defaultCountry', 'select', 'Default Country'))->key()->renderInput(Setting::getValue('country_default'), ['options' => $allCountries]) !!}

                    <button type="submit" class="btn btn-success mt-2">Save Countries</button>

                </form>

            </div>
        </div>
    </div>
</div>

@section('scripts')
    <script>
        $(document).ready(function() {

            let allCountries = [];
            $('#countries option').each((index, obj) => {
                allCountries.push($(obj).val());
            });

            $('#selectAll').click(function() {
                $('#countries').val(allCountries).trigger('change');
            });

            $('#clearAll').click(function() {
                $('#countries').val([]).trigger('change');
            });

        });
    </script>
@append
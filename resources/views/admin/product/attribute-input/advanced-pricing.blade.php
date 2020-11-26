<?php
/**
 * @var CoasterCommerce\Core\Model\Product\Attribute $attribute
 * @var Illuminate\Support\ViewErrorBag $errors
 * @var mixed $value
 */
$groups = [0 => '-- All Groups --'] + CoasterCommerce\Core\Model\Customer\Group::pluck('name', 'id')->toArray();
?>

<div class="form-group row">
    <div class="col-sm-12">

        <p>Lowest applicable price will be displayed for customers. If no rules match, the base price will be used.</p>

        <table class="table table-hover advanced-pricing">
            <thead>
            <tr>
                <th>Group</th>
                <th>Minimum Quantity</th>
                <th>Price</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            @php
                if ($errors && $advancedPricingPrices = old('advanced_pricing', [])) {
                    // reload unsaved input data on errors
                    $advancedPricingData = [];
                    foreach ($advancedPricingPrices as $i => $price) {
                        if (is_null($price)) continue;
                        // formbuilder will automatically load old data, so blank model is good enough
                        $advancedPricingData[$i] = new \CoasterCommerce\Core\Model\Product\AdvancedPricing();
                    }
                } else {
                    $advancedPricingData = $value ?: [];
                }
            @endphp
            <tr class="d-none" id="templatePricingRow">
                <td>
                    {{ $formBuilder->select('advanced_pricing[{i}][group_id]', $groups, null, ['class' => 'form-borderless w-100', 'disabled']) }}
                </td>
                <td>
                    {{ $formBuilder->text('advanced_pricing[{i}][min_quantity]', null, ['class' => 'w-100', 'disabled']) }}
                </td>
                <td>
                    {{ $formBuilder->text('advanced_pricing[{i}][price]', null, ['class' => 'w-100', 'disabled']) }}
                </td>
                <td>
                    <a href="javascript:void(0)"><i class="fa fa-trash"></i></a>
                </td>
            </tr>
            @foreach($advancedPricingData as $i => $advancedPricing)
                <tr>
                    <td>
                        {{ $formBuilder->select('advanced_pricing['.$i.'][group_id]', $groups, $advancedPricing->group_id, ['class' => 'form-borderless w-100'. ($errors->has('advanced_pricing.'.$i.'.group_id') ? ' is-invalid' : '')]) }}
                        <small class="form-text text-danger">{{ $errors->first('advanced_pricing.'.$i.'.group_id') }}</small>
                    </td>
                    <td>
                        {{ $formBuilder->text('advanced_pricing['.$i.'][min_quantity]', $advancedPricing->min_quantity ?: null, ['class' => 'w-100'. ($errors->has('advanced_pricing.'.$i.'.min_quantity') ? ' is-invalid' : '')]) }}
                        <small class="form-text text-danger">{{ $errors->first('advanced_pricing.'.$i.'.min_quantity') }}</small>
                    </td>
                    <td>
                        {{ $formBuilder->text('advanced_pricing['.$i.'][price]', $advancedPricing->price, ['class' => 'w-100'. ($errors->has('advanced_pricing.'.$i.'.price') ? ' is-invalid' : '')]) }}
                        <small class="form-text text-danger">{{ $errors->first('advanced_pricing.'.$i.'.price') }}</small>
                    </td>
                    <td>
                        <a href="javascript:void(0)"><i class="fa fa-trash"></i></a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        <button class="btn btn-info" type="button" id="addPricing">Add Group/Tiered Pricing Rule</button>

    </div>
</div>

@section('scripts')
    <script>
        $(document).ready(function() {

            var newRowI = {{ count($advancedPricingData) }};

            $('.advanced-pricing').on('click', '.fa-trash', function (e) {
                $(e.target).closest('tr').remove();
            });

            $('#addPricing').click(function () {
                $('.advanced-pricing tbody').append(
                    $('#templatePricingRow').prop('outerHTML').replace(/d-none/, '').replace(/disabled/g, '').replace(/{i}/g, newRowI)
                );
                newRowI++;
            });

        });
    </script>
@append
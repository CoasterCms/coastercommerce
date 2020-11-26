<?php
/**
 * @var CoasterCommerce\Core\Model\Product $product
 * @var CoasterCommerce\Core\Model\Product\Attribute $attribute
 * @var Illuminate\Support\ViewErrorBag $errors
 * @var Collective\Html\FormBuilder $formBuilder
 * @var array $value // $product->variation_attributes
 */
$value = $value ?: []; // make sure not null on new new prod
$images = $product->images ?: new \CoasterCommerce\Core\Model\Product\Attribute\Model\FileModel\FileValue();
$images = ['' => '-'] + $images->selectOptions();
?>

<div class="form-group row">
    <div class="col-sm-12">

        <p>Manage configurable attributes for use in creating variations.</p>

        <table class="table table-hover" id="productVariationAttributes">
            <thead>
            <tr>
                <th style="width: 40%;">Attribute</th>
                <th>Options</th>
            </tr>
            </thead>
            <tbody>
            @php
            if (!$errors || !($variationAttributes = old('attributes.variation_attributes', []))) {
                $variationAttributes = [];
                foreach ($value as $attributeName => $attributeOptions) {
                    $options = [];
                    foreach ($attributeOptions as $optionName => $optionData) {
                        $options[] = $optionData + ['value' => $optionName, 'display' => ''];
                    }
                    $variationAttributes[] = ['attribute' => $attributeName, 'option' => $options];
                }
            }
            @endphp
            <tr class="d-none">
                <td>
                    {{ $formBuilder->text('attributes[variation_attributes][{i}][attribute]', null, ['class' => 'mb-1 w-100', 'disabled']) }}
                    <button type="button" class="btn btn-small btn-outline-danger delete-attribute"><span class="fa fa-trash"></span> Remove Attribute</button>
                </td>
                <td>
                    <div class="row mb-1" data-opt="0">
                        <div class="col-6">
                            {{ $formBuilder->text('attributes[variation_attributes][{i}][option][0][value]', null, ['placeholder' => 'Option Value', 'class' => 'form-borderhless w-100', 'disabled']) }}
                        </div>
                        <div class="col-5">
                            {{ $formBuilder->text('attributes[variation_attributes][{i}][option][0][display]', null, ['placeholder' => 'Display Info', 'class' => 'form-borderhless w-100', 'disabled']) }}
                        </div>
                        <div class="col-1">
                            <a href="javascript:void(0)" class="delete-option"><i class="fa fa-trash"></i></a>
                        </div>
                    </div>
                    <button type="button" class="btn btn-small btn-outline-info add-option"><span class="fa fa-plus"></span> Add Option</button>
                </td>
            </tr>
            @foreach($variationAttributes as $i => $variationAttribute)
                <tr>
                    <td>
                        {{ $formBuilder->text('attributes[variation_attributes]['.$i.'][attribute]', $variationAttribute['attribute'], ['class' => 'mb-1 w-100']) }}
                        <button type="button" class="btn btn-small btn-outline-danger delete-attribute"><span class="fa fa-trash"></span> Remove Attribute</button>
                    </td>
                    <td>
                        @foreach($variationAttribute['option'] as $o => $optionData)
                        <div class="row mb-1" data-opt="{{ $o }}">
                            <div class="col-6">
                                {{ $formBuilder->text('attributes[variation_attributes]['.$i.'][option]['.$o.'][value]', $optionData['value'], ['placeholder' => 'Option Value', 'class' => 'form-borderhless w-100']) }}
                            </div>
                            <div class="col-5">
                                {{ $formBuilder->text('attributes[variation_attributes]['.$i.'][option]['.$o.'][display]', $optionData['display'], ['placeholder' => 'Display Info', 'class' => 'form-borderhless w-100']) }}
                            </div>
                            <div class="col-1">
                                <a href="javascript:void(0)" class="delete-option"><i class="fa fa-trash"></i></a>
                            </div>
                        </div>
                        @endforeach
                        <button type="button" class="btn btn-small btn-outline-info add-option"><span class="fa fa-plus"></span> Add Option</button>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        <button class="btn btn-info mb-5" type="button" id="addAttribute">Add Attribute</button>

        <p class="mb-3">
            Product variations can be created from the attributes above. They can have separate sku, price, stock and weight from the base product.<br />
            If price, stock or weight fields are left blank, values will be taken from base product.<br />
            Stocks fields only used if base product has enabled stock managed option. (blank = unlimited, 0 = out of stock, 1+ = in stock)
        </p>

        <div class="table-responsive">
            <table class="table table-hover" id="productVariations">
                <thead>
                <tr>
                    <th></th>
                    <th>Enabled</th>
                    <th>Sku</th>
                    <th>Price (Fixed?)</th>
                    <th>Stock</th>
                    <th>Weight</th>
                    <th>Image</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                @php
                    if ($errors && $variationsPostData = old('variations', [])) {
                        // reload unsaved input data on errors
                        $variations = [];
                        foreach ($variationsPostData as $i => $variationPostData) {
                            // formbuilder will automatically load old data, so blank model is good enough
                            if (is_numeric($i)) $variations[$i] = new \CoasterCommerce\Core\Model\Product\Variation();;
                        }
                        $variations = collect($variations);
                    } else {
                        $variations = $product->variations ? $product->variations->keyBy('id') : collect([]);
                    }
                @endphp
                <tr class="d-none" data-id="{i}">
                    <td>
                        <i class="fa fa-arrows-alt"></i>
                    </td>
                    <td>
                        <input type="hidden" class="custom-control-input" name="variations[{i}][enabled]" value="0" disabled>
                        <div class="custom-control custom-switch">
                            {{ $formBuilder->checkbox('variations[{i}][enabled]', 1, true, ['class' => 'custom-control-input', 'id' => 'variations_{i}_enabled', 'disabled']) }}
                            <label class="custom-control-label" for="variations_{i}_enabled"></label>
                        </div>
                    </td>
                    <td>
                        {{ $formBuilder->text('variations[{i}][sku]', null, ['class' => 'w-100', 'disabled']) }}
                    </td>
                    <td>
                        <div class="input-group">
                            {{ $formBuilder->text('variations[{i}][price]', null, ['class' => 'form-control ', 'disabled']) }}
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    {{ $formBuilder->checkbox('variations[{i}][fixed_price]', 1, null, ['disabled']) }}
                                </div>
                            </div>
                        </div>
                    </td>
                    <td>
                        {{ $formBuilder->text('variations[{i}][stock_qty]', null, ['class' => 'w-100', 'disabled']) }}
                    </td>
                    <td>
                        {{ $formBuilder->text('variations[{i}][weight]', null, ['class' => 'w-100', 'disabled']) }}
                    </td>
                    <td>
                        {{ $formBuilder->select('variations[{i}][image]', $images, null, ['class' => 'form-borderless w-100 v-images', 'disabled']) }}
                    </td>
                    <td>
                        <a href="javascript:void(0)"><i class="fa fa-trash"></i></a>
                    </td>
                </tr>
                @foreach($variations as $i => $variation)
                    <tr data-id="{{ $i }}">
                        <td>
                            <i class="fa fa-arrows-alt"></i>
                        </td>
                        <td>
                            <input type="hidden" class="custom-control-input" name="variations[{{ $i }}][enabled]" value="0">
                            <div class="custom-control custom-switch">
                                {{ $formBuilder->checkbox('variations['.$i.'][enabled]', 1, $variation->enabled, ['class' => 'custom-control-input', 'id' => 'variations_'.$i.'_enabled']) }}
                                <label class="custom-control-label" for="variations_{{ $i }}_enabled"></label>
                            </div>
                        </td>
                        @php $attributeValues = $variation->variationArray(); @endphp
                        @foreach($variationAttributes as $variationAttribute)
                        @php
                            $variantAOptions = array_map(function ($optionData) {return $optionData['value'];}, $variationAttribute['option']) ?: [];
                            $variantAValue = array_key_exists($variationAttribute['attribute'], $attributeValues) ? $attributeValues[$variationAttribute['attribute']] : null; // formbuilder will pick up old() values if null
                        @endphp
                        <td class="variation-conf">
                            {{ $formBuilder->select('variations['.$i.']['.$variationAttribute['attribute'].']', array_combine($variantAOptions, $variantAOptions), $variantAValue, ['class' => 'form-borderless w-100']) }}
                        </td>
                        @endforeach
                        <td>
                            {{ $formBuilder->text('variations['.$i.'][sku]', $variation->sku ?: null, ['class' => 'w-100'. ($errors->has('variations.'.$i.'.sku') ? ' is-invalid' : '')]) }}
                            <small class="form-text text-danger">{{ $errors->first('variations.'.$i.'.sku') }}</small>
                        </td>
                        <td>
                            <div class="input-group">
                                {{ $formBuilder->text('variations['.$i.'][price]', $variation->price ?: null, ['class' => 'form-control '. ($errors->has('variations.'.$i.'.price') ? ' is-invalid' : '')]) }}
                                <div class="input-group-append">
                                    <div class="input-group-text">
                                        {{ $formBuilder->checkbox('variations['.$i.'][fixed_price]', 1, $variation->fixed_price, ['class' => ''. ($errors->has('variations.'.$i.'.fixed_price') ? ' is-invalid' : '')]) }}
                                    </div>
                                </div>
                            </div>
                            <small class="form-text text-danger">{{ $errors->first('variations.'.$i.'.price') }}</small>
                        </td>
                        <td>
                            {{ $formBuilder->text('variations['.$i.'][stock_qty]', is_null($variation->stock_qty) ? null : $variation->stock_qty, ['class' => 'w-100'. ($errors->has('variations.'.$i.'.stock_qty') ? ' is-invalid' : ''), 'placeholder' => 'unlimited']) }}
                            <small class="form-text text-danger">{{ $errors->first('variations.'.$i.'.stock_qty') }}</small>
                        </td>
                        <td>
                            {{ $formBuilder->text('variations['.$i.'][weight]', $variation->weight ?: null, ['class' => 'w-100'. ($errors->has('variations.'.$i.'.weight') ? ' is-invalid' : '')]) }}
                            <small class="form-text text-danger">{{ $errors->first('variations.'.$i.'.weight') }}</small>
                        </td>
                        <td>
                            {{ $formBuilder->select('variations['.$i.'][image]', $images, $variation->image, ['class' => 'form-borderless w-100 v-images']) }}
                        </td>
                        <td>
                            <a href="javascript:void(0)"><i class="fa fa-trash"></i></a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <button class="btn btn-info" type="button" id="addVariation">Add Variation</button>

    </div>
</div>

@section('scripts')
    <script>
        $(document).ready(function() {

            window.addEventListener('attributes_images_update', function (e) {
                let imageOptions = e.detail;
                imageOptions.unshift({caption: '-', path: ''});
                $('.v-images').each(function () {
                    let currentVal = $(this).val();
                    $(this).find('option').remove();
                    for (let i = 0; imageOptions.length > i; i++) {
                        $(this).append($('<option></option>')
                            .attr('value', imageOptions[i].path)
                            .text(imageOptions[i].caption)
                            .attr('selected', currentVal === imageOptions[i].path)
                        );
                    }
                });
            }, false);

            // attributes table
            let attrRowI = parseInt({{ $variationAttributes ? max(array_keys($variationAttributes)) + 1 : 0 }});
            $('#addAttribute').click(function () {
                let productVariationAttributesTBody = $('#productVariationAttributes tbody');
                productVariationAttributesTBody.append(
                    productVariationAttributesTBody.find('tr.d-none').prop('outerHTML').replace(/d-none/, '').replace(/disabled/g, '').replace(/{i}/g, attrRowI)
                );
                attrRowI++;
                updateVariations();
            });

            $('#productVariationAttributes').on('click', '.delete-attribute', function (e) {
                $(this).closest('tr').remove();
                updateVariations();
            }).on('click', '.delete-option', function () {
                if ($(this).closest('td').children().length > 1) {
                    let optionValue = $(this).closest('.row').find('input[name$="[value]"]').val();
                    let optionName = $(this).closest('tr').find('td:first-child input[name$="[attribute]"]').val();
                    $('#productVariations > tbody > tr').each(function () {
                       if ($(this).find('select[name$="['+optionName+']"]').val() === optionValue) {
                           $(this).remove();
                       }
                    });
                    $(this).closest('.row').remove();
                    updateVariations();
                } else {
                    commerceAlert('danger', 'Attributes must have at least one option, to remove attribute click red "Remove Attribute" button.')
                }
            }).on('click', '.add-option', function (e) {
                let optRowI = 0;
                let optionsTdEl = $(this).closest('tr').find('td:nth-child(2)');
                optionsTdEl.find('> .row').each(function () {
                    optRowI = Math.max(parseInt($(this).data('opt'))+1, optRowI);
                });
                $(optionsTdEl.find('> .row:first-child').prop('outerHTML').replace(/\[option]\[0]/g, '[option]['+optRowI+']').replace(/opt="0"/g, 'opt="'+optRowI+'"').replace(/value=".*"/g, '')).insertBefore($(this));
                updateVariations();
            }).on('change', 'input', function () {
                updateVariations();
            });
            updateVariations();

            // variations table
            let varRowI = parseInt({{ $variations->count() ? max(array_keys($variations->toArray())) + 1 : 0 }});
            $('#addVariation').click(function () {
                let productVariationsTBody = $('#productVariations tbody');
                productVariationsTBody.append(
                    productVariationsTBody.find('tr.d-none').prop('outerHTML').replace(/d-none/, '').replace(/disabled/g, '').replace(/{i}/g, varRowI)
                );
                $('.custom-switch > input').unbind('change').change(switchLabelUpdate);
                updateVariations();
                varRowI++;
            });

            $('#productVariations').on('click', '.fa-trash', function (e) {
                $(this).closest('tr').remove();
            });

            // attribute update in variations table
            function updateVariations() {
                let configurableAttributes = [];
                let configurableAttributeOptions = {};
                $('#productVariationAttributes > tbody > tr').each(function () {
                    if ($(this).hasClass('d-none')) return;
                    let attributeName = $(this).find('input[name*="attribute"]').val();
                    if (attributeName) {
                        configurableAttributes.push(attributeName);
                        configurableAttributeOptions[attributeName] = [];
                        $(this).find('td:nth-child(2) > .row').each(function () {
                            configurableAttributeOptions[attributeName].push($(this).find('input[name$="[value]"]').val());
                        });
                    }
                });
                $('#productVariations th.variation-conf').remove();
                let variationHeader = '';
                for (let i = 0; configurableAttributes.length > i; i++) {
                    variationHeader += '<th class="variation-conf">' + configurableAttributes[i] + '</th>';
                }
                $(variationHeader).insertAfter($('#productVariations thead th:first-child'));
                $('#productVariations > tbody > tr').each(function () {
                    if (isNaN($(this).data('id'))) return;
                    let variationCells = '';
                    for (let i = 0; configurableAttributes.length > i; i++) {
                        let attributeName = configurableAttributes[i];
                        let selectEl = $(this).find('select[name^="variations['+$(this).data('id')+']"]').eq(i);
                        let newSelectEl = $('<select name="variations['+$(this).data('id')+']['+attributeName+']"></select>').addClass('form-borderless w-100');
                        let newSelectedIndex = configurableAttributeOptions[attributeName].indexOf(selectEl.val()) > -1
                            ? configurableAttributeOptions[attributeName].indexOf(selectEl.val())
                            : selectEl.prop('selectedIndex');
                        for (let j = 0; configurableAttributeOptions[attributeName].length > j; j++) {
                            newSelectEl.append($('<option></option>')
                                .attr('value', configurableAttributeOptions[attributeName][j])
                                .text(configurableAttributeOptions[attributeName][j])
                                .attr('selected', j === newSelectedIndex)
                            );
                        }
                        variationCells += '<td class="variation-conf">'+newSelectEl.prop('outerHTML')+'</td>';
                    }
                    $(this).find('td.variation-conf').remove();
                    $(variationCells).insertAfter($(this).find('td:first-child'));
                });
            }

            // sort variations
            new Sortable(document.querySelectorAll('#productVariations tbody')[0], {
                animation: 150,
                handle: '.fa-arrows-alt',
                draggable: 'tr',
                fallbackOnBody: true,
                swapThreshold: 0.2
            });

        });
    </script>
@append
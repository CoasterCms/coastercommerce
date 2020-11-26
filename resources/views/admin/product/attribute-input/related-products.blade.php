<?php
/**
 * @var CoasterCommerce\Core\Model\Product\Attribute $attribute
 * @var Illuminate\Support\ViewErrorBag $errors
 * @var mixed $value
 */
$productArray = (new \CoasterCommerce\Core\Model\Product\Attribute\OptionSource\Product())->optionsData();
$relationTypes = ['related' => 'Related', 'cross_sell' => 'Cross Sell', 'up_sell' => 'Up Sell'];
?>

<div class="form-group row">
    <div class="col-sm-12">

        <table class="table table-hover related-product">
            <thead>
            <tr>
                <th>Product</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            @php
                if ($errors && $relatedProducts = old('related_product', [])) {
                    // reload unsaved input data on errors
                    $relatedProductsData = [];
                    foreach ($relatedProducts as $i => $productId) {
                        if (is_null($productId)) continue;
                        // formbuilder will automatically load old data, so blank model is good enough
                        $relatedProductsData[$i] = new \CoasterCommerce\Core\Model\Product\Related();
                        $relatedProductsData[$i]->pivot = collect();
                    }
                } else {
                    $relatedProductsData = $value ?: [];
                }
            @endphp
            <tr class="d-none" id="templateRelatedRow">
                <td>
                    {{ $formBuilder->select('related_product[{i}][related_product_id]', $productArray, null, ['class' => 'form-borderless', 'disabled']) }}
                </td>
                <td>
                    {{ $formBuilder->select('related_product[{i}][relation][]', $relationTypes, ['related'], ['class' => 'form-borderless', 'multiple', 'disabled']) }}
                </td>
                <td>
                    <a href="javascript:void(0)"><i class="fa fa-trash"></i></a>
                </td>
            </tr>
            @foreach($relatedProductsData as $i => $relatedProduct)
                <tr>
                    <td>
                        {{ $formBuilder->select('related_product['.$i.'][related_product_id]', $productArray, $relatedProduct->id, ['class' => 'form-borderless select2-p']) }}
                    </td>
                    <td>
                        {{ $formBuilder->select('related_product['.$i.'][relation][]', $relationTypes, array_keys(array_filter(array_intersect_key($relatedProduct->pivot->toArray(), $relationTypes))), ['class' => 'form-borderless select2', 'multiple']) }}
                    </td>
                    <td>
                        <a href="javascript:void(0)"><i class="fa fa-trash"></i></a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        <button class="btn btn-info" type="button" id="addRelatedProduct">Add Related Product</button>

    </div>
</div>

@section('scripts')
    <script>
        $(document).ready(function() {

            var newRowI = {{ count($relatedProductsData) }};

            var relatedProdTable = $('.related-product');

            relatedProdTable.on('click', '.fa-trash', function (e) {
                $(e.target).closest('tr').remove();
            });

            $('#addRelatedProduct').click(function () {
                $('.related-product tbody').append(
                    $('#templateRelatedRow').prop('outerHTML').replace(/d-none/, '').replace(/disabled/g, '').replace(/{i}/g, newRowI)
                );
                relatedProdTable.find('td:nth-child(1) select:enabled').each(function () {
                    if (!$(this).hasClass('select2-p')) {
                        $(this).addClass('select2-p').select2({'width':'100%',minimumInputLength: 3});
                    }
                });
                relatedProdTable.find('td:nth-child(2) select:enabled').each(function () {
                    if (!$(this).hasClass('select2')) {
                        $(this).addClass('select2').select2({'width':'100%'});
                    }
                });
                newRowI++;
            });

        });
    </script>
@append
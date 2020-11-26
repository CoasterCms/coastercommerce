<?php
/**
 * @var Illuminate\Database\Eloquent\Collection|\CoasterCommerce\Core\Model\Product[] $products
 * @var Illuminate\Database\Eloquent\Collection|\CoasterCommerce\Core\Model\Product\Attribute[] $massAttributes
 * @var string $idString
 * @var int $step
 */
$nullProduct = new \CoasterCommerce\Core\Model\Product();
?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">

                <form action="{{ $step == 1 ? route('coaster-commerce.admin.product.mass-action') : route('coaster-commerce.admin.product.mass-action.update') }}" method="post">

                    {!! csrf_field() !!}

                    <input type="hidden" name="action" value="update" />
                    <input type="hidden" name="ids" value="{{ $idString }}" />

                    <div class="row">
                        <h1 class="card-title col-sm-7 mb-5">
                            Update Products ({{ $products->count() }} selected)
                        </h1>

                        <div class="col-sm-5 mb-5 text-right">
                            <button type="button" id="showProducts" class="btn btn-success mb-2">
                                Show Products
                            </button>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">

                            <div id="productList" class="d-none">
                                <h2>Selected Products</h2>
                                <p class="mb-5">
                                    @foreach($products as $product)
                                        {{ $product->name }}<br />
                                    @endforeach
                                <p>
                            </div>

                            @if ($step == 2)
                            <div>
                                <h2>Modify Attributes</h2>

                                @foreach($massAttributes as $massAttribute)
                                    <input type="hidden" name="attribute_ids[{{ $massAttribute->id }}]" value="1" />
                                @endforeach

                                @foreach($massAttributes as $massAttribute)
                                    {!! $massAttribute->renderInput($nullProduct) !!}
                                @endforeach

                                <button class="btn btn-success mt-5" type="submit">
                                    Confirm Update
                                </button>

                            </div>
                            @else

                                <h2>Select Attributes to Update</h2>

                                @foreach($massAttributes as $massAttribute)
                                    {!! (new \CoasterCommerce\Core\Renderer\Admin\Attribute($massAttribute->id, 'switch', $massAttribute->name))->key('attribute_ids')->renderInput(0) !!}
                                @endforeach

                                <button class="btn btn-success mt-5" type="submit">
                                    Update Attributes
                                </button>

                            @endif

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

            let productListEl = $('#productList');
            $('#showProducts').click(function () {
                productListEl.toggleClass('d-none');
                $(this).html(productListEl.hasClass('d-none') ? 'Show Products' : 'Hide Products');
            });

        });
    </script>
@append
<?php
/**
 * @var Illuminate\Database\Eloquent\Collection|\CoasterCommerce\Core\Model\Product[] $products
 * @var string $idString
 */
?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">

                <form action="{{ route('coaster-commerce.admin.product.mass-action.delete') }}" method="post">

                    {!! csrf_field() !!}

                    <input type="hidden" name="ids" value="{{ $idString }}" />

                    <div class="row">
                        <div class="col-12">

                            <h1 class="card-title">
                                Delete Products ({{ $products->count() }} selected)
                            </h1>

                            <p>
                            @foreach($products as $product)
                                {{ $product->name }}<br />
                            @endforeach
                            <p>

                            <button class="btn btn-danger mt-5" type="submit">
                                Confirm Delete (cannot be undone)
                            </button>

                        </div>
                    </div>

                </form>

            </div>
        </div>
    </div>
</div>
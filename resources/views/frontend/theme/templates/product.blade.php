<?php
/**
 * @var CoasterCommerce\Core\Model\Category $category
 * @var CoasterCommerce\Core\Model\Product $product
 */
use CoasterCommerce\Core\Currency\Format;
?>

{!! $cart->view('sections.head') !!}

<div class="container">

    <div class="row">
        <div class="col-sm-12 text-left">
            <h2 class="mt-5 mb-5">{{ $product->name }}</h2>
            {!! $coasterCommerceCrumbs->render('breadcrumbs.default.') !!}
        </div>
    </div>

    <div class="row">

        <div class="col-md-6">
            <div class="prodpage_prodimage">
                <img src="{{ $product->getImage() }}" class="img-fluid" alt="{{ $product->name }}" />
            </div>
        </div>

        <div class="col-md-6 text-left">

            {!! $product->description !!}

            <div class="prod_details mt-2">
                <p>Product Code: <strong>{{ $product->sku }}</strong></p>
                <p>Excl. VAT: <strong class="price">{!! new Format($product->getPrice(0, 'ex')) !!}</strong></p>
                <p>Incl. VAT: {!! new Format($product->getPrice(0, 'inc')) !!}</p>
            </div>

            @if (stripos($product->stock_status, 'In Stock') !== false)

                {!! $formBuilder->open(['route' => 'coaster-commerce.frontend.checkout.cart.add', 'class' => 'form-inline']) !!}

                {!! $formBuilder->hidden('product_id', $product->id) !!}
                <div class="form-group">
                    {!! $formBuilder->number('qty', 1, ['class' => 'form-control qty_form']) !!}
                </div>
                <button type="submit" class="btn btn-default">
                    <i class="fas fa-shopping-cart"></i> &nbsp; Add to basket
                </button>

                {!! $formBuilder->close() !!}
            @else
                <form class="form-group" action="{{ route('coaster-commerce.frontend.stock.notify') }}" method="post">
                    {!! csrf_field() !!}
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <input type="text" name="email" class="form-control" value="{{ $cart->getCustomer() ? $cart->getCustomer()->email : '' }}" placeholder="Enter your email..." required>
                    <button type="submit" class="btn btn-default ">
                        <i class="fas fa-envelope"></i> &nbsp; Notify me when this product is back in stock
                    </button>
                </form>
            @endif

        </div>

    </div>

    @php
        /** @var \Illuminate\Support\Collection $relatedProducts */
        $relatedProducts = $product->relatedProducts;
        if (!$relatedProducts->count()) {
            $category = $category ?: $product->categories->first();
            $relatedProducts = $category ? $category->products()->where('id', '!=', $product->id)->get() : $relatedProducts;
        }
        $relatedProducts = $relatedProducts->shuffle()->slice(0, 6);
    @endphp

    @if ($relatedProducts->count())

        <div class="row">
            <div class="col-sm-12 text-center">
                <h2 class="mt-5 mb-5 homeh2">Related Products</h2>
            </div>
        </div>

        @foreach($relatedProducts as $product)
            @if ($loop->first || $loop->iteration%6 == 1)
                <div class="row justify-content-center">
            @endif
                    <div class="col-md-2 col-sm-4">
                        <div class="card p-3">
                            <div class="imgholder">
                                <a href="{{ $product->getUrl($category) }}">
                                    <img src="{{ $product->getImage() }}" class="img-fluid" alt="{{ $product->name }}" />
                                </a>
                            </div>
                            <h4>
                                <a href="{{ $product->getUrl($category) }}">{{ $product->name }}</a>
                            </h4>
                            <p class="price">
                                <span>{!! new Format($product->price) !!}</span>
                            </p>
                            <div class="row">
                                <div class="col-sm-12">
                                    <a class="btn btn-primary" href="{{ $product->getUrl($category) }}">More info</a>
                                </div>
                            </div>
                        </div>
                    </div>
            @if ($loop->last || $loop->iteration%6 == 0)
                </div>
            @endif
        @endforeach

    @endif

</div>

{!! $cart->view('sections.footer') !!}

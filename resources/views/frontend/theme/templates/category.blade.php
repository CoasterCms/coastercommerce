<?php
/**
 * @var CoasterCommerce\Core\Model\Category $category
 * @var int $pageSize
 */
use CoasterCommerce\Core\Currency\Format;
$subCats = $category->getCategories();
?>

{!! $cart->view('sections.head') !!}

<div class="container">

    <div class="row">
        <div class="col-sm-12 text-left">
            <h2 class="mt-5 mb-5">{{ $category->name }}</h2>
            {!! $coasterCommerceCrumbs->render('breadcrumbs.default.') !!}
            {!! $category->content !!}
        </div>
    </div>

    @php $products = $category->getProducts()->orderBy('name')->paginate(!empty($pageSize) ? $pageSize : 30) @endphp

    @if ($subCats->count())
        @foreach($subCats as $subCat)
            @if ($loop->first || $loop->iteration % 3 == 1)
            <div class="row justify-content-center category_top">
            @endif

                <div class="col-md-4">
                    <div class="card p-3">
                        <a href="{{ $subCat->getUrl() }}">
                            <img src="{{ \Croppa::url($subCat->getImage(), 480, 300) }}" class="img-fluid" alt="{{ $subCat->name }}">
                        </a>
                        <h3><a href="{{ $subCat->getUrl() }}">{{ $subCat->name }}</a></h3>
                    </div>
                </div>

            @if ($loop->last || $loop->iteration % 3 == 0)
            </div>
            @endif
        @endforeach
        @if ($products->count())
        <div class="row">
            <div class="col-sm-12 text-left">
                <h2 class="mt-5 mb-5 homeh2">All {{ $category->name }}</h2>
            </div>
        </div>
        @endif
    @endif

    {!! $cart->view('sections.product-pagination', ['products' => $products]) !!}

    <div class="row justify-content-center">
        @foreach($products as $product)
            <div class="col-md-2 col-sm-4">
                <div class="card p-3">
                    <div class="imgholder">
                        <a href="{{ $product->getUrl($category) }}">
                            <img src="{{ $product->image ? \Croppa::url($product->getImage(), 200) : $product->getImage() }}" class="img-fluid" alt="{{ $product->name }}" />
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
        @endforeach
    </div>

    {!! $cart->view('sections.product-pagination', ['products' => $products]) !!}

</div>

{!! $cart->view('sections.footer') !!}

<?php
/** @var \CoasterCommerce\Core\Session\Cart $cart */
/** @var \CoasterCommerce\Core\Model\Customer\WishList $wishList */
/** @var \Collective\Html\FormBuilder $formBuilder */
/** @var bool $canEdit */
?>{!! $pb->section('head') !!}

<div class="container">
    {!! $coasterCommerceCrumbs->render('breadcrumbs.default.') !!}
    <div class="row">
        <div class="col-sm-3 order-sm-1 order-2">
            {!! app('coaster-commerce.customer-menu')->render('menus.customer.') !!}
        </div>
        <div class="col-sm-9 order-sm-2 order-1">
            <div class="row">
                <div class="col-sm-12">
                    <h1>{{ $wishList->name }}</h1>
                    @if (!$wishList->items->count())
                        <p>Looks like your list is empty, add a product and come back.</p>
                    @endif
                    @if ($canEdit)
                        <div class="list-actions">
                            <button type="button" class="btn btn-default mb-4" data-toggle="modal" data-target="#renameListModal">Rename List</button> &nbsp;
                            @if ($wishList->items->count())
                            <a href="{{ route('coaster-commerce.frontend.customer.wishlist.clear', ['id' => $wishList->id]) }}" class="btn btn-default mb-4 confirm" data-confirm="you want to clear this list">Clear List</a> &nbsp;
                            @endif
                            <a href="{{ route('coaster-commerce.frontend.customer.wishlist.delete', ['id' => $wishList->id]) }}" class="btn btn-default mb-4 confirm" data-confirm="you want to delete this list">Delete List</a> &nbsp;
                            @if ($wishList->items->count())
                            <button class="btn btn-primary mb-4" data-toggle="modal" data-target="#shareListModal">Share List</button>
                            @endif
                        </div>
                    @endif
                    @if ($wishList->items->count())
                    <div class="table-responsive">
                        <table class="w-100 baskettable mb-5">
                            <thead>
                                <tr>
                                    <th class="pl-3">Item</th>
                                    <th></th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                            @foreach($wishList->items as $item)
                            <tr>
                                <td>
                                    <div class="imgholder">
                                        <a href="{{ $item->product->getUrl() }}">
                                            <img src="{{ \Croppa::url($item->product->getImage(), 300, 300, ['resize']) }}" class="img-fluid" alt="{{ $item->product->name }}">
                                        </a>
                                    </div>
                                </td>
                                <td>
                                    <div class="nameholder">
                                        <h4><a href="{{ $item->product->getUrl() }}">{{ $item->product->name }}</a></h4>
                                        @if ($item->variation)
                                        <dl class="variation">
                                            @foreach($item->variation->variationArray() as $optionLabel => $optionValue)
                                                <dt>{{ $optionLabel }}:</dt>
                                                <dd>{!! $optionValue !!}</dd>
                                            @endforeach
                                        </dl>
                                        @endif
                                        <p>ref: {{ $item->product->sku }}</p>
                                        <span class="amount">
                                            @if ($item->variation)
                                                {!! new \CoasterCommerce\Core\Currency\Format($item->variation->getPrice()) !!}
                                            @elseif ($item->product)
                                                {!! ($item->product->variations->count() ? 'From ' : '') . new \CoasterCommerce\Core\Currency\Format($item->product->fromPrice()) !!}
                                            @endif
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    @if ($item->variation_id ? $item->variation->inStock() : $item->product->inStock())
                                        {!! $formBuilder->open(['route' => 'coaster-commerce.frontend.checkout.cart.add']) !!}
                                        {!! $formBuilder->hidden('product_id', $item->product_id) !!}
                                        {!! $formBuilder->hidden('variation_id', $item->variation_id) !!}
                                        <div class="product-quantity">
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <button type="button" data-action="minus" class="btn"><span class="fa fa-minus"></span></button>
                                                </div>
                                                <input
                                                        type="number" name="qty" value="1" title="Qty"
                                                        step="1" min="0" size="4" class="form-control qty"
                                                >
                                                <div class="input-group-append">
                                                    <button type="button" data-action="plus" class="btn"><span class="fa fa-plus"></span></button>
                                                </div>
                                            </div>
                                        </div>
                                        <button type="submit" class="btn btn-primary">
                                            Add to Cart
                                        </button>
                                        {!! $formBuilder->close() !!}
                                    @else
                                        <p class="text-danger">Out of stock</p>
                                    @endif
                                    @if ($canEdit)
                                    <a href="{{ route('coaster-commerce.frontend.customer.wishlist.remove-item', ['id' => $item->id]) }}" class="d-block mt-2">
                                        &raquo; Remove from list
                                    </a>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="renameListModal" tabindex="-1" aria-labelledby="renameListModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            {!! $formBuilder->open(['url' => route('coaster-commerce.frontend.customer.wishlist.rename', ['id' => $wishList->id])]) !!}
            <div class="modal-header">
                <h5 class="modal-title" id="renameListModalLabel">Rename List</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <label for="name" class="col-sm-2 col-form-label">Name</label>
                    <div class="col-sm-10">
                        {!! $formBuilder->text('name', null, ['class' => 'form-control', 'id' => 'name', 'placeholder' => 'Enter new name ...', 'required']) !!}
                    </div>
                </div>
            </div>
            <div class="modal-footer list-actions">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
            {!! $formBuilder->close() !!}
        </div>
    </div>
</div>

<div class="modal fade" id="shareListModal" tabindex="-1" aria-labelledby="shareListModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            {!! $formBuilder->open(['url' => route('coaster-commerce.frontend.customer.wishlist.share', ['id' => $wishList->id])]) !!}
            <div class="modal-header">
                <h5 class="modal-title" id="shareListModalLabel">Share List</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>
                    Share your list with the link or use form below.<br /><br />
                    <b>Public Link:</b> {{ route('coaster-commerce.frontend.customer.wishlist.view', ['id' => $wishList->id, 'share_key' => $wishList->share_key]) }}
                </p>
                <div class="form-group">
                    <label for="wishListEmails">Email Addresses</label>
                    {!! $formBuilder->textarea('emails', null, ['class' => 'form-control', 'id' => 'wishListEmails', 'rows' => 3, 'placeholder' => 'Enter one email address per line ...', 'required']) !!}
                </div>
                <div class="form-group">
                    <label for="wishListName">Your Name</label>
                    {!! $formBuilder->text('name', $cart->getCustomer() ? $cart->getCustomer()->defaultBillingAddress()->fullName() : null, ['class' => 'form-control', 'id' => 'wishListName', 'placeholder' => 'Enter you name ...', 'required']) !!}
                </div>
                <div class="form-group">
                    <label for="wishMessage">Message</label>
                    {!! $formBuilder->textarea('message', null, ['class' => 'form-control', 'id' => 'wishMessage', 'rows' => 5, 'placeholder' => 'Enter message to send with list ...']) !!}
                </div>
            </div>
            <div class="modal-footer list-actions">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary">Send Email</button>
            </div>
            {!! $formBuilder->close() !!}
        </div>
    </div>
</div>

@section('coastercommerce.scripts')
    <script>

        $('.product-quantity button').click(function () {
            var quantityEl = $(this).closest('.product-quantity').find('.qty');
            var quantityVal = quantityEl.val();
            $(this).data('action') === 'minus' ? quantityVal-- : quantityVal++;
            quantityEl.val(quantityVal < 0 ? 0 : quantityVal).trigger('change');
        });
    </script>
@append

{!! $pb->section('footer') !!}

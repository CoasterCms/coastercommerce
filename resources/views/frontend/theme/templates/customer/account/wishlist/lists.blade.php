<?php
/** @var \CoasterCommerce\Core\Model\Customer\WishList[]|\Illuminate\Database\Eloquent\Collection $wishLists */
/** @var \CoasterCommerce\Core\Model\Customer\WishList $currentList */
/** @var \CoasterCommerce\Core\Session\Cart $cart */
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
                    <h1>My Lists</h1>
                    @if ($cart->getCustomerId())
                        <a href="{{ route('coaster-commerce.frontend.customer.wishlist.new') }}" class="btn btn-primary float-right" style="margin-top:-60px">New List</a>
                    @endif
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12">
                @if (!$wishLists->count())
                    <p>You have no lists, add a product first.</p>
                @endif
                @if (!$cart->getCustomerId())
                    <p>Login or register to permanently save your list as well setup multiple lists!</p>
                @endif
                @if ($wishLists->count())
                    @if ($wishLists->count() > 1)
                        <p>Active list: <a href="{{ route('coaster-commerce.frontend.customer.wishlist.view', ['id' => $currentList->id]) }}">{{ $currentList->name }}</a></p>
                    @endif
                    <table class="w-100 table-bordered">
                        <tbody>
                        @foreach($wishLists as $wishList)
                            <tr>
                                <td>
                                    <a href="{{ route('coaster-commerce.frontend.customer.wishlist.view', ['id' => $wishList->id]) }}">{{ $wishList->name }}</a>
                                </td>
                                <td>
                                    {{ $wishList->items->count() }} product{{ $wishList->items->count() == 1 ? '' : 's' }}
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                @endif
                </div>
            </div>
        </div>
    </div>
</div>

{!! $pb->section('footer') !!}

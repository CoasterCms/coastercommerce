<?php
/**
 * @var CoasterCommerce\Core\Model\Promotion $promotion
 * @var \Collective\Html\FormBuilder $formBuilder
 */
use CoasterCommerce\Core\Renderer\Admin\Attribute;
?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">

                <form action="{{ route('coaster-commerce.admin.promotion.save', ['id' => $promotion->exists ? $promotion->id : 0]) }}" id="promoForm" method="post">

                    {!! csrf_field() !!}

                    <div class="row">

                        <h1 class="card-title col-sm-7 mb-5">
                            @if ($promotion->exists)
                                Edit {{ $promotion->name }}
                            @else
                                New Promotion
                            @endif
                        </h1>

                        <div class="col-sm-5 mb-5 text-right">
                            @if ($promotion->exists)
                                <a href="{{ route('coaster-commerce.admin.promotion.delete', ['id' => $promotion->id]) }}" class="btn btn-danger confirm mb-2" data-confirm="you wish to delete this promotion">
                                    <i class="fas fa-trash-alt"></i> &nbsp; Delete
                                </a> &nbsp;
                            @endif
                            <button name="saveAction" value="continue" class="btn btn-success mb-2">
                                <i class="fas fa-save"></i> &nbsp; Save
                            </button> &nbsp;
                            <button name="saveAction" value="return" class="btn btn-success mb-2">
                                <i class="fas fa-arrow-circle-left"></i> &nbsp; Save & return to promotion list
                            </button>
                        </div>

                    </div>

                    <div class="row">

                        <div class="col-md-2 mb-3">
                            <ul class="nav nav-pills flex-column" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" id="general-tab" data-toggle="tab" href="#general" role="tab" aria-controls="general" aria-selected="true">
                                        General
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="customer-tab" data-toggle="tab" href="#customer" role="tab" aria-controls="customer">
                                        Customer / Groups
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="options-tab" data-toggle="tab" href="#options" role="tab" aria-controls="options">
                                        {{ $promotion->type == 'item' ? 'Catalogue' : 'Subtotal / Shipping' }}
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="coupons-tab" data-toggle="tab" href="#coupons" role="tab" aria-controls="coupons">
                                        Coupon Codes
                                    </a>
                                </li>
                            </ul>
                        </div>

                        <div class="col-md-10">
                            <div class="tab-content">
                                <div class="tab-pane fade show active" id="general" role="tabpanel" aria-labelledby="general-tab">
                                    {!! $formBuilder->hidden('attributes[type]', $promotion->type) !!}
                                    {!! (new Attribute('enabled', 'switch', 'Enabled'))->renderInput($promotion->enabled) !!}
                                    {!! (new Attribute('name', 'text', 'Name'))->renderInput($promotion->name) !!}
                                    {!! (new Attribute('active_from', 'date', 'Active From'))->renderInput($promotion->active_from ? $promotion->active_from->format('Y-m-d H:i:s') : null) !!}
                                    {!! (new Attribute('active_to', 'date', 'Active Until'))->renderInput($promotion->active_to ? $promotion->active_to->format('Y-m-d H:i:s') : null) !!}
                                    {!! (new Attribute('discount_type', 'select', 'Discount Type'))->renderInput($promotion->discount_type, ['options' => ['percent' => 'Percentage', 'fixed' => 'Fixed Amount']]) !!}
                                    {!! (new Attribute('discount_amount', 'text', 'Discount Amount'))->renderInput($promotion->discount_amount) !!}
                                    {!! (new Attribute('priority', 'text', 'Priority', ['help' => 'Determines the order promotions are applied. (all non-visible ones will be applied first, then the visible ones)']))->renderInput($promotion->priority ?: 1) !!}
                                    {!! (new Attribute('is_last', 'switch', 'Works with other Promotions ?'))->renderInput(!$promotion->is_last) !!}
                                </div>
                                <div class="tab-pane fade show" id="customer" role="tabpanel" aria-labelledby="customer-tab">
                                    <p>Apply promotion to specific customers or groups. To apply to everyone, leave fields blank.</p>
                                    {!! (new Attribute('customer_ids', 'select-multiple', 'Customers'))->renderInput($promotion->customers->pluck('id')->toArray(), ['options' => $customers]) !!}
                                    {!! (new Attribute('group_ids', 'select-multiple', 'Groups'))->renderInput($promotion->customerGroups->pluck('id')->toArray(), ['options' => $groups]) !!}
                                </div>
                                <div class="tab-pane fade show" id="options" role="tabpanel" aria-labelledby="options-tab">
                                    @if ($promotion->type == 'item')
                                    <p>Apply promotion to exclude or only include products in certain categories.</p>
                                    {!! (new Attribute('include_categories', 'select', 'Rule'))->renderInput($promotion->include_categories, ['options' => [1 => 'Only include products in specific categories', 0 => 'Exclude selected categories']]) !!}
                                    {!! (new Attribute('category_ids', 'select-multiple', 'Categories'))->renderInput($promotion->categories->pluck('id')->toArray(), ['options' => $categories]) !!}
                                    <p class="mt-5">Apply promotion to exclude or only include certain products.</p>
                                    {!! (new Attribute('include_products', 'select', 'Rule'))->renderInput($promotion->include_products, ['options' => [1 => 'Only include selected products', 0 => 'Exclude selected products']]) !!}
                                    {!! (new Attribute('product_ids', 'select-multiple', 'Products'))->renderInput($promotion->products->pluck('id')->toArray(), ['options' => $products]) !!}
                                    @else
                                        {!! (new Attribute('apply_to_subtotal', 'select', 'Apply to Subtotal'))->renderInput($promotion->apply_to_subtotal, ['options' => [1 => 'Yes', 0 => 'No']]) !!}
                                        {!! (new Attribute('apply_to_shipping', 'select', 'Apply to Shipping'))->renderInput($promotion->apply_to_shipping, ['options' => [1 => 'Yes', 0 => 'No']]) !!}
                                    @endif
                                </div>
                                <div class="tab-pane fade show" id="coupons" role="tabpanel" aria-labelledby="coupons-tab">
                                    <p>To stop promotions applying automatically, coupon codes can be set. Clear all codes to make promotion automatic again.</p>
                                    <p>For unlimited coupon uses, leave uses field blank.</p>
                                    <table class="table table-hover">
                                        <thead>
                                        <tr>
                                            <th>Coupon code</th>
                                            <th>Uses Available</th>
                                            <th>Created On</th>
                                            <th></th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @php
                                            if ($errors && $coupons = old('coupon', [])) {
                                                foreach ($coupons as $i => $couponFieldVales) {
                                                    // formbuilder will automatically load old data, so blank model is good enough
                                                    $coupons[$i] = new \CoasterCommerce\Core\Model\Promotion\Coupon();
                                                }
                                                $coupons = collect($coupons);
                                            } else {
                                                $coupons = $promotion->coupons;
                                            }
                                        @endphp
                                        <tr class="d-none" id="templateCouponRow">
                                            <td>
                                                {{ $formBuilder->text('coupon[{i}][code]', null, ['class' => 'w-100', 'disabled']) }}
                                            </td>
                                            <td>
                                                {{ $formBuilder->text('coupon[{i}][uses_left]', null, ['class' => 'w-100', 'disabled']) }}
                                            </td>
                                            <td>
                                                New
                                            </td>
                                            <td>
                                                <a href="javascript:void(0)"><i class="fa fa-trash"></i></a>
                                            </td>
                                        </tr>
                                        @foreach($coupons as $i => $coupon)
                                            <tr data-id="{{ $coupon->id }}">
                                                <td>
                                                    {{ $formBuilder->hidden('coupon['.$i.'][code]', $coupon->code) }}
                                                    {{ $formBuilder->text('', $coupon->code, ['class' => 'w-100', 'disabled']) }}
                                                </td>
                                                <td>
                                                    {{ $formBuilder->text('coupon['.$i.'][uses_left]', $coupon->uses_left, ['class' => 'w-100'. ($errors->has('coupon.'.$i.'.uses_left') ? ' is-invalid' : '')]) }}
                                                    <small class="form-text text-danger">{{ $errors->first('coupon.'.$i.'.uses_left') }}</small>
                                                </td>
                                                <td>
                                                    {{ $coupon->created_at->format('H:i:s d/m/y') }}
                                                </td>
                                                <td>
                                                    <a href="javascript:void(0)"><i class="fa fa-trash"></i></a>
                                                </td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                    {!! $formBuilder->select('coupon_deleted_ids[]', $coupons->where('id', '>', 0)->pluck('id', 'id')->toArray(), null, ['style' => 'display:none', 'multiple', 'id' => 'couponDeletedIds']) !!}
                                    <button class="btn btn-info" type="button" id="addCoupon">Add New Coupon</button>
                                </div>
                            </div>
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

            let firstError = $('.is-invalid').first();
            if (firstError) {
                $('#' + firstError.closest('.tab-pane').attr('id') + '-tab').click();
            }

            var newRowI = {{ count($coupons) }};

            let couponDeletedIds = [];
            function couponDeleteId(couponId) {
                if (couponId && couponDeletedIds.indexOf(couponId) === -1) {
                    couponDeletedIds.push(couponId);
                    $('#couponDeletedIds').val(couponDeletedIds);
                }
            }

            $('#coupons').on('click', '.fa-trash', function (e) {
                let couponTr = $(e.target).closest('tr');
                couponDeleteId(couponTr.data('id'));
                couponTr.remove();
            }).on('change', "input", function (e) {
                let couponTr = $(e.target).closest('tr');
                couponTr.attr('data-updated', '1');
            });

            $('#promoForm').submit(function() {
                $('#coupons tbody tr[data-updated!=1] input').removeAttr('name');
                return true;
            });

            $('#addCoupon').click(function () {
                $('#coupons tbody').append(
                    $('#templateCouponRow').prop('outerHTML').replace(/d-none/, '').replace(/disabled/g, '').replace(/{i}/g, newRowI)
                );
                newRowI++;
            });

        });
    </script>
@append
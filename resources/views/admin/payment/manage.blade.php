<?php
/**
 * @var \Collective\Html\FormBuilder $formBuilder
 * @var \CoasterCommerce\Core\Model\Order\Shipping\AbstractPayment[] $methods
 */
use CoasterCommerce\Core\Renderer\Admin\Attribute;
?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">

                {!! $formBuilder->open(['route' => 'coaster-commerce.admin.system.payment.save', 'files' => true]) !!}

                    {!! csrf_field() !!}

                    <div class="row">

                        <h1 class="card-title col-sm-7 mb-5">Payment Methods</h1>

                        <div class="col-sm-5 mb-5 text-right">
                            <button name="saveAction" value="continue" class="btn btn-success mb-2">
                                <i class="fas fa-save"></i> &nbsp; Save
                            </button>
                        </div>

                    </div>

                    <div class="accordion">
                    @foreach($methods as $method)

                        <div class="card mb-3 border-bottom">
                            <div class="card-header" id="{{ $method->code }}_head">
                                <h2 class="mb-0" data-toggle="collapse" data-target="#{{ $method->code }}_collapse" aria-expanded="true">
                                    <i class="fa fa-credit-card"></i> &nbsp; {{ $method->type() . ' - ' . $method->name }}
                                </h2>
                            </div>

                            <div id="{{ $method->code }}_collapse" class="collapse" aria-labelledby="{{ $method->code }}_head">
                                <div class="card-body">

                                    {!! (new Attribute('active', 'switch', 'Enabled'))->key($method->code)->renderInput($method->active) !!}
                                    {!! (new Attribute('name', 'text', 'Method Name'))->key($method->code)->renderInput($method->name) !!}
                                    {!! (new Attribute('description', 'wysiwyg', 'Method Description'))->key($method->code)->renderInput($method->description) !!}
                                    {!! (new Attribute('sort_value', 'text', 'Sort Value'))->key($method->code)->renderInput($method->sort_value) !!}
                                    {!! (new Attribute('order_status', 'select', 'Order Status'))->key($method->code)->renderInput($method->order_status ?: \CoasterCommerce\Core\Model\Order::STATUS_PROCESSING, ['options' => $statuses]) !!}

                                    <div class="form-group row">
                                        <label class="col-sm-3 col-form-label" for="attributes_sort_value">
                                            Order Amount Restrictions (Inc Vat.)
                                        </label>
                                        <div class="col-sm-9">
                                            <div class="row">
                                                <div class="col-6">
                                                    <div class="input-group">
                                                        <div class="input-group-prepend">
                                                            <label for="{{ $method->code }}_min_p" class="input-group-text">Min</label>
                                                        </div>
                                                        <input type="text" name="{{ $method->code }}[min_cart_total]" value="{{ $method->min_cart_total }}" class="form-control" id="{{ $method->code }}_min_p">
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="input-group">
                                                        <div class="input-group-prepend">
                                                            <label for="{{ $method->code }}_max_p" class="input-group-text">Max</label>
                                                        </div>
                                                        <input type="text" name="{{ $method->code }}[max_cart_total]" value="{{ $method->max_cart_total }}" class="form-control" id="{{ $method->code }}_max_p">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {!! $method->renderCustomFields() !!}

                                </div>
                            </div>
                        </div>

                    @endforeach
                    </div>

                {!! $formBuilder->close() !!}

            </div>
        </div>
    </div>
</div>
<?php
/**
 * @var CoasterCommerce\Core\Model\Promotion $promotion
 */
use CoasterCommerce\Core\Renderer\Admin\Attribute;
?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">

                <form action="{{ route('coaster-commerce.admin.promotion.add') }}" method="get">

                    {!! csrf_field() !!}

                    <div class="row">

                        <h1 class="card-title col-sm-7 mb-5">
                            New Promotion
                        </h1>

                        <div class="col-sm-5 mb-5 text-right">
                            <a href="{{ route('coaster-commerce.admin.promotion.list') }}" class="btn btn-info mb-2">
                                <i class="fas fa-arrow-circle-left"></i> &nbsp; Return to promotion list
                            </a>
                        </div>

                    </div>

                    <div class="row">

                        <div class="col-12">
                            {!! (new Attribute('type', 'select', 'Type'))->key(null)->renderInput('cart', ['options' => ['cart' => 'Cart Promotion', 'item' => 'Item/Product Promotion']]) !!}
                        </div>
                        <div class="col-sm-9 offset-sm-3">
                            <button class="btn btn-primary mt-3">Setup Promotion</button>
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

        });
    </script>
@append
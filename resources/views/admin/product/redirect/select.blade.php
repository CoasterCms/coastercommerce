<?php
/**
 * @var array $redirectProducts
 * @var array $categoryOptions
 * @var array $productOptions
 */
use CoasterCommerce\Core\Renderer\Admin\Attribute;
?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-12">

                        <h1 class="card-title mb-3">
                            Delete & Redirect Products
                        </h1>

                        <div class="row">

                            <div class="col-sm-4">
                                <h2 class="mb-5">Select Redirect Location</h2>
                                {!! (new Attribute('entity_type', 'select', 'Type'))->key()->renderInput(null, ['options' => ['' => 'No Redirect', 'c' => 'Redirect to Category', 'p' => 'Redirect to Product']]) !!}
                                <div class="d-none" id="categoryType">
                                    {!! (new Attribute('category', 'select', 'Category'))->key()->renderInput(null, ['options' => $categoryOptions]) !!}
                                </div>
                                <div class="d-none" id="productType">
                                    {!! (new Attribute('product', 'select', 'Product'))->key()->renderInput(key($productOptions), ['options' => $productOptions]) !!}
                                </div>
                                <div class="text-right d-none" id="applyNote">
                                    (click on products to apply redirect) or
                                    <button type="button" class="applyAll btn btn-success mt-2">
                                        Apply All
                                    </button>
                                </div>
                            </div>

                            <div class="col-sm-8">

                                <div class="row mb-5">
                                    <div class="col-8">
                                        <h2>Apply to Products</h2>
                                    </div>
                                    <div class="col-4 text-right">
                                        <button type="button" class="applyAll btn btn-success">
                                            Apply All
                                        </button>
                                    </div>
                                </div>

                                <form action="{{ route('coaster-commerce.admin.product.redirect.apply') }}" method="post">
                                    {!! csrf_field() !!}
                                    <table class="table table-striped" id="redirectsTable">
                                        <thead>
                                            <tr>
                                                <th>Product</th>
                                                <th>Redirect To</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($redirectProducts as $product)
                                            <tr>
                                                <td>{{ $product->name }}</td>
                                                <td>
                                                    <input type="hidden" name="redirect[{{ $product->id }}]" />
                                                    <span>No Redirect</span>
                                                </td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                    <button class="btn btn-danger mt-5" type="submit">
                                        Confirm Delete & Redirect (cannot be undone)
                                    </button>
                                </form>

                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@section('scripts')
    <script>
        $(document).ready(function() {

            let currentRedirect = '';
            let redirectsList = {'' : 'No Redirect'};

            let applyNote = $('#applyNote');
            let entityTypeEl = $('#entity_type');
            let typeEls = [$('#categoryType'), $('#productType')];
            entityTypeEl.change(function () {
                typeEls.forEach(function (typeEl) {
                    if (!typeEl.hasClass('d-none')) {
                        typeEl.addClass('d-none');
                    }
                });

                applyNote.removeClass('d-none');
                if ($(this).val() === 'c') {
                    typeEls[0].removeClass('d-none');
                    $('#category').trigger('change');
                } else if ($(this).val() === 'p') {
                    typeEls[1].removeClass('d-none');
                    $('#product').trigger('change');
                } else {
                    applyNote.addClass('d-none');
                    currentRedirect = '';
                }
            });

            function currentRedirectUpdate(e) {
                let targetEl = $(e.target);
                currentRedirect = entityTypeEl.val() + ':' + targetEl.val();
                if (!(currentRedirect in redirectsList)) {
                    redirectsList[currentRedirect] = targetEl.closest('.form-group').find('label').text().trim() + ': ' + targetEl.find('option:selected').text();
                }
            }

            $('#category').change(currentRedirectUpdate);
            $('#product').change(currentRedirectUpdate);

            $('#redirectsTable tbody tr').click(function () {
                $(this).find('input').val(currentRedirect);
                $(this).find('td:eq(1) span').html(redirectsList[currentRedirect]);
            });

            $('.applyAll').click(function () {
                $('#redirectsTable tbody tr').trigger('click');
            });

        });
    </script>
@append
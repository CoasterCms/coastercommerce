<?php
/**
 * @var CoasterCommerce\Core\Model\Product $product
 * @var CoasterCommerce\Core\Model\Product\Attribute\Group[] $groups
 */
use \CoasterCommerce\Core\Model\Setting;
$redirectOmDelete = Setting::getValue('catalogue_redirect');
?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">

                <form action="{{ route('coaster-commerce.admin.product.save', ['id' => $product->exists ? $product->id : 0]) }}" method="post">

                    {!! csrf_field() !!}

                    <div class="row">

                        <h1 class="card-title col-sm-7 mb-5">
                            @if ($product->exists)
                                Edit {{ $product->name }} [{{ $product->sku }}]
                           @else
                                New Product
                           @endif
                        </h1>

                        <div class="col-sm-5 mb-5 text-right">
                            @if ($product->exists)
                            <a href="{{ route($redirectOmDelete ? 'coaster-commerce.admin.product.redirect.single' : 'coaster-commerce.admin.product.delete', ['id' => $product->id])  }}"
                               class="btn btn-danger {{ $redirectOmDelete ? '' : 'confirm ' }}mb-2" data-confirm="you wish to delete this product">
                                <i class="fas fa-trash-alt"></i> &nbsp; Delete
                            </a> &nbsp;
                            <a href="{{ $product->getUrl() }}" target="_blank" class="btn btn-info mb-2">
                                <i class="fas fa-eye"></i> &nbsp; View
                            </a> &nbsp;
                            @endif
                            <button name="saveAction" value="continue" class="btn btn-success mb-2">
                                <i class="fas fa-save"></i> &nbsp; Save
                            </button> &nbsp;
                            <button name="saveAction" value="return" class="btn btn-success mb-2">
                                <i class="fas fa-arrow-circle-left"></i> &nbsp; Save & return to product list
                            </button>
                        </div>

                    </div>

                    <div class="row">

                        <div class="col-md-2 mb-3">
                            <ul class="nav nav-pills flex-column" role="tablist">
                                @foreach($groups as $group)
                                    @if ($group->adminProductAttributes()->count())
                                    <li class="nav-item">
                                        <a class="nav-link{{ $loop->first ? ' active' : null }}" id="{{ $group->tabName() }}-tab" data-toggle="tab" href="#{{ $group->tabName() }}" role="tab" aria-controls="home" aria-selected="true">
                                            {!! $group->name !!}
                                        </a>
                                    </li>
                                    @endif
                                @endforeach
                            </ul>
                        </div>

                        <div class="col-md-10">
                            <div class="tab-content">
                                @foreach($groups as $group)
                                    @if ($group->adminProductAttributes()->count())
                                    <div class="tab-pane fade {{ $loop->first ? ' show active' : null }}" id="{{ $group->tabName() }}" role="tabpanel" aria-labelledby="{{ $group->tabName() }}-tab">
                                        @foreach($group->adminProductAttributes() as $attribute)
                                            {!! $attribute->renderInput($product) !!}
                                        @endforeach
                                    </div>
                                    @endif
                                @endforeach
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

            @if (!$product->exists)
            let autoUrl = true, nameInput = $('#attributes_name'), urlInput = $('#attributes_url_key');
            nameInput.change(function () {
                if (autoUrl) {
                    urlInput.val(parsePageUrl(nameInput.val()));
                }
            });
            urlInput.change(function () {
                autoUrl = urlInput.val() === '';
            });

            function parsePageUrl(url) {
                return url.toLowerCase()
                    .replace(/[^\w-]/g, '-')
                    .replace(/-{2,}/g, '-')
                    .replace(/^-+/g, '')
                    .replace(/-+$/g, '');
            }
            @endif

        });
    </script>
@append
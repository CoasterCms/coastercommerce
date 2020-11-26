<?php
/**
 * @var CoasterCommerce\Core\Model\Redirect $redirect
 */
use CoasterCommerce\Core\Renderer\Admin\Attribute;
?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">

                <form action="{{ route('coaster-commerce.admin.redirect.save', ['id' => $redirect->exists ? $redirect->id : 0]) }}" method="post">

                    {!! csrf_field() !!}

                    <div class="row">

                        <h1 class="card-title col-sm-7 mb-5">
                            @if ($redirect->exists)
                                Edit Redirect
                            @else
                                New Redirect
                            @endif
                        </h1>

                        <div class="col-sm-5 mb-5 text-right">
                            @if ($redirect->exists)
                                <a href="{{ route('coaster-commerce.admin.redirect.delete', ['id' => $redirect->id]) }}" class="btn btn-danger mb-2">
                                    <i class="fas fa-trash-alt"></i> &nbsp; Delete
                                </a> &nbsp;
                            @endif
                            <button name="saveAction" value="continue" class="btn btn-success mb-2">
                                <i class="fas fa-save"></i> &nbsp; Save
                            </button> &nbsp;
                            <button name="saveAction" value="return" class="btn btn-success mb-2">
                                <i class="fas fa-arrow-circle-left"></i> &nbsp; Save & return to redirect list
                            </button>
                        </div>

                    </div>

                    <div class="row">

                        <div class="col-md-2 mb-3">
                            <ul class="nav nav-pills flex-column" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" id="general-tab" data-toggle="tab" href="#general" role="tab" aria-controls="home" aria-selected="true">
                                        General
                                    </a>
                                </li>
                            </ul>
                        </div>

                        <div class="col-md-10">
                            <div class="tab-content">
                                <div class="tab-pane fade show active" id="general" role="tabpanel" aria-labelledby="general-tab">
                                    {!! (new Attribute('url', 'text', 'Url'))->renderInput($redirect->url) !!}
                                    {!! (new Attribute('product_id', 'select', 'Redirect to Product'))->renderInput($redirect->product_id, ['options' => $productOptions]) !!}
                                    {!! (new Attribute('category_id', 'select', 'Redirect to Category'))->renderInput($redirect->category_id, ['options' => $categoryOptions]) !!}
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

        });
    </script>
@append
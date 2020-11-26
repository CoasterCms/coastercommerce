<?php
/**
 * @var CoasterCommerce\Core\Model\Category $category
 * @var string $contentFieldsHtml
 */
use CoasterCommerce\Core\Renderer\Admin\Attribute;
?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">

                <form action="{{ route('coaster-commerce.admin.category.save', ['id' => $category->exists ? $category->id : 0]) }}" method="post">

                    {!! csrf_field() !!}

                    <div class="row">

                        <h1 class="card-title col-sm-7 mb-5">
                            @if ($category->exists)
                                Edit {{ $category->name }}
                            @else
                                New Category
                            @endif
                        </h1>

                        <div class="col-sm-5 mb-5 text-right">
                            @if ($category->exists)
                                <a href="{{ route('coaster-commerce.admin.category.delete', ['id' => $category->id]) }}" class="btn btn-danger confirm mb-2" data-confirm="you wish to delete this category">
                                    <i class="fas fa-trash-alt"></i> &nbsp; Delete
                                </a> &nbsp;
                                <a href="{{ $category->getUrl() }}" target="_blank" class="btn btn-info mb-2">
                                    <i class="fas fa-eye"></i> &nbsp; View
                                </a> &nbsp;
                            @endif
                            <button name="saveAction" value="continue" class="btn btn-success mb-2">
                                <i class="fas fa-save"></i> &nbsp; Save
                            </button> &nbsp;
                            <button name="saveAction" value="return" class="btn btn-success mb-2">
                                <i class="fas fa-arrow-circle-left"></i> &nbsp; Save & return to category list
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
                                <li class="nav-item">
                                    <a class="nav-link" id="general-tab" data-toggle="tab" href="#content" role="tab" aria-controls="home" aria-selected="true">
                                        Content
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="meta-tab" data-toggle="tab" href="#meta" role="tab" aria-controls="home" aria-selected="true">
                                        Meta Information
                                    </a>
                                </li>
                            </ul>
                        </div>

                        <div class="col-md-10">
                            <div class="tab-content">
                                <div class="tab-pane fade show active" id="general" role="tabpanel" aria-labelledby="general-tab">
                                    {!! (new Attribute('enabled', 'switch', 'Enabled'))->renderInput($category->exists ? $category->enabled : 1) !!}
                                    {!! (new Attribute('name', 'text', 'Name'))->renderInput($category->name) !!}
                                    {!! (new Attribute('path', 'category', 'Parent Category'))->renderInput($category->parentId()) !!}
                                    {!! (new Attribute('anchor', 'switch', 'Is anchor', ['help' => 'Anchors also contain all products from their sub categories']))->renderInput($category->exists ? $category->anchor : 1) !!}
                                    {!! (new Attribute('menu', 'switch', 'Show in menu'))->renderInput($category->menu) !!}
                                    {!! (new Attribute('featured', 'switch', 'Featured'))->renderInput($category->featured) !!}
                                </div>
                                <div class="tab-pane fade show" id="content" role="tabpanel" aria-labelledby="content-tab">

                                    @if ($category->exists)

                                        <div class="form-group row">
                                            <label class="col-sm-12 col-form-label" for="cat_images">
                                                Images
                                            </label>
                                            <div class="col-sm-12">
                                                <input id="cat_images" name="images[]" type="file" multiple>
                                            </div>
                                        </div>

                                    @php
                                        $imageData = $category->getImages();
                                    @endphp
                                    @section('scripts')
                                        <script>
                                            jQuery(document).ready(function($) {
                                                var fileInputEl = $('#cat_images');
                                                fileInputEl.fileinput({
                                                    allowedFileExtensions: ['jpg', 'png', 'gif'],
                                                    uploadUrl: ccRouter.route('coaster-commerce.admin.category-file.upload', {id: '{{ $category->id }}'}),
                                                    deleteUrl: ccRouter.route('coaster-commerce.admin.category-file.delete', {id: '{{ $category->id }}'}),
                                                    uploadAsync: false,
                                                    initialPreviewAsData: true,
                                                    initialPreview: {!! json_encode($imageData->getFiles()) !!},
                                                    initialPreviewConfig: {!! json_encode($imageData->getFilesConfig()) !!},
                                                    overwriteInitial: false,
                                                    theme: 'fa'
                                                }).on("filebatchselected", function(event, files) {
                                                    fileInputEl.fileinput("upload");
                                                }).on('filesorted', function(event, params) {
                                                    $.post(
                                                        ccRouter.route('coaster-commerce.admin.category-file.sort', {id: '{{ $category->id }}'}),
                                                        {stack: params.stack}
                                                    );
                                                });
                                            });
                                        </script>
                                    @append

                                    @else

                                        <div class="form-group row">
                                            <label class="col-sm-3 col-form-label" for="cat_images">
                                                Images
                                            </label>
                                            <div class="col-sm-9">
                                                [Save category first to edit]
                                            </div>
                                        </div>

                                    @endif

                                    {!! $contentFieldsHtml !!}
                                </div>
                                <div class="tab-pane fade show" id="meta" role="tabpanel" aria-labelledby="meta-tab">
                                    {!! (new Attribute('url_key', 'text', 'Url'))->renderInput($category->url_key) !!}
                                    {!! (new Attribute('meta_title', 'text', 'Meta Title', ['length-guide' => '40,60']))->renderInput($category->meta_title) !!}
                                    {!! (new Attribute('meta_description', 'textarea', 'Meta Description', ['length-guide' => '120,156']))->renderInput($category->meta_description) !!}
                                    {!! (new Attribute('meta_keywords', 'textarea', 'Meta Keywords', ['length-guide' => '40,60']))->renderInput($category->meta_keywords) !!}
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

            @if (!$category->exists)
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
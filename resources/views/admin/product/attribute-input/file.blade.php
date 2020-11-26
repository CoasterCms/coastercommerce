<?php
/**
 * @var CoasterCommerce\Core\Model\Product\Attribute $attribute
 * @var Illuminate\Support\ViewErrorBag $errors
 * @var mixed $value
 */
?>

@if ($product->exists)

    <div class="form-group row">
        <label class="col-sm-12 col-form-label" for="{{ $attribute->id() . $attribute->code }}">
            {{ $attribute->name }}
        </label>
        <div class="col-sm-12">
            <input type="hidden" name="fileInput[{{ $attribute->code }}]" value="1">
            <input id="{{ $attribute->id() . $attribute->code }}" name="{{ $attribute->fieldName() }}[]" type="file" multiple>
        </div>
    </div>

    @section('scripts')
    <script>
        jQuery(document).ready(function($) {
            function codeParam() {
                return {code: '{{ $attribute->code }}'};
            }
            function updateFilesEvent() {
                setTimeout(function () {
                    let filePaths = fileInputEl.data('fileinput').initialPreview;
                    let fileConfig = fileInputEl.data('fileinput').initialPreviewConfig;
                    let fileInfo = [];
                    for (let i = 0; i < fileConfig.length; i++) {
                        fileInfo[i] = fileConfig[i];
                        fileInfo[i].path = filePaths[i];
                    }
                    var customEvent = new CustomEvent('{{ $attribute->id() . $attribute->code }}_update', {detail: fileInfo});
                    window.dispatchEvent(customEvent);
                }, 1000);
            }
            var fileInputEl = $('#{{ $attribute->id() . $attribute->code }}');
            fileInputEl.fileinput({
                allowedFileExtensions: ['jpg', 'png', 'gif'],
                uploadUrl: ccRouter.route('coaster-commerce.admin.product-file.upload', {id: '{{ $product->id }}'}),
                uploadExtraData: codeParam,
                deleteUrl: ccRouter.route('coaster-commerce.admin.product-file.delete', {id: '{{ $product->id }}'}),
                deleteExtraData: codeParam,
                uploadAsync: false,
                initialPreviewAsData: true,
                initialPreview: {!! json_encode($value->getFiles()) !!},
                initialPreviewConfig: {!! json_encode($value->getFilesConfig()) !!},
                overwriteInitial: false,
                theme: 'fa'
            }).on("filebatchselected", function(event, files) {
                fileInputEl.fileinput("upload");
            }).on('filesorted', function(event, params) {
                $.post(
                    ccRouter.route('coaster-commerce.admin.product-file.sort', {id: '{{ $product->id }}'}),
                    {stack: params.stack, code: '{{ $attribute->code }}'}
                );
            }).on('filebatchuploadsuccess', function(event, data) {
                updateFilesEvent();
            }).on('filedeleted', function(event, key, jqXHR, data) {
                updateFilesEvent();
            });
        });
    </script>
    @append

@else

    <div class="form-group row">
        <label class="col-sm-3 col-form-label" for="{{ $attribute->id() . $attribute->code }}">
            {{ $attribute->name }}
        </label>
        <div class="col-sm-9">
            [Save product first to edit]
        </div>
    </div>

@endif
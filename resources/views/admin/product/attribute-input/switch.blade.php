<?php
/**
 * @var CoasterCommerce\Core\Model\Product\Attribute $attribute
 * @var Illuminate\Support\ViewErrorBag $errors
 * @var mixed $value
 */
?>

<div class="form-group row">
    <label class="col-sm-3 col-form-label" for="{{ $attribute->id() . $attribute->code }}">
        {{ $attribute->name }}
        @if (array_key_exists('help', $meta))
            <span class="badge badge-primary" data-toggle="tooltip" title="{{ $meta['help'] }}">?</span>
        @endif
    </label>
    <div class="col-sm-9">
        <input type="hidden" class="custom-control-input" name="{{ $attribute->fieldName() }}" value="0">
        <div class="custom-control custom-switch">
            <input type="checkbox" class="custom-control-input" id="{{ $attribute->id() . $attribute->code }}" name="{{ $attribute->fieldName() }}" value="1" {{ old($attribute->fieldKey(), $value) ? 'checked' : '' }}>
            <label class="custom-control-label" for="{{ $attribute->id() . $attribute->code }}"></label>
        </div>
    </div>
</div>

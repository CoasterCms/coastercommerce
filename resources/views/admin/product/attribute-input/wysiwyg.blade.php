<?php
/**
 * @var CoasterCommerce\Core\Model\Product\Attribute $attribute
 * @var Illuminate\Support\ViewErrorBag $errors
 * @var mixed $value
 */
?>

<div class="form-group row">
    <label class="col-sm-12 col-form-label" for="{{ $attribute->id() . $attribute->code }}">
        {{ $attribute->name }}
    </label>
    <div class="col-sm-12">
        <textarea
                id="{{ $attribute->id() . $attribute->code }}"
                name="{{ $attribute->fieldName() }}"
                class="tinymce"
        >{{ old($attribute->fieldKey(), $value) }}</textarea>
    </div>
</div>
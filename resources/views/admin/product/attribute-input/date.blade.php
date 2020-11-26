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
    </label>
    <div class="col-sm-9">
        <input
                type="text"
                id="{{ $attribute->id() . $attribute->code }}"
                name="{{ $attribute->fieldName() }}"
                value="{{ old($attribute->fieldKey(), $value) }}"
                autocomplete="off"
                class="form-control datetime {!! $errors->has($attribute->fieldKey()) ? 'is-invalid' : '' !!}"
        />
        @if ($errorMessage = $errors->first($attribute->fieldKey()))
            <small class="form-text text-danger">{{ $errorMessage }}</small>
        @elseif (isset($note))
            <small class="form-text text-muted">&raquo; {{ $note }}</small>
        @endif
    </div>
</div>
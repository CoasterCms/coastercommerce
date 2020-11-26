<?php
/**
 * @var CoasterCommerce\Core\Model\Product\Attribute $attribute
 * @var Illuminate\Support\ViewErrorBag $errors
 * @var mixed $value
 * @var array $options
 */
?>

<div class="form-group row">
    <label class="col-sm-3 col-form-label" for="{{ $attribute->id() . $attribute->code }}">
        {{ $attribute->name }}
    </label>
    <div class="col-sm-9">
        <select
                id="{{ $attribute->id() . $attribute->code }}"
                name="{{ $attribute->fieldName() }}[]"
                class="{{ count($options) > 200 ? 'select2-p' : 'select2' }} form-control {!! $errors->has($attribute->fieldKey()) ? 'is-invalid' : '' !!}"
                multiple
                @if (isset($disabled) && $disabled)
                disabled
                @endif
        >
            @foreach($options as $optionValue => $optionName)
                <option value="{{ $optionValue }}" {{ in_array($optionValue, old($attribute->fieldKey(), $value ?: [])) ? 'selected' : '' }}>{{ $optionName }}</option>
            @endforeach
        </select>
        @if ($errorMessage = $errors->first($attribute->fieldKey()))
            <small class="form-text text-danger">{{ $errorMessage }}</small>
        @elseif (isset($note))
            <small class="form-text text-muted">&raquo;{{ $note }}</small>
        @endif
    </div>
</div>


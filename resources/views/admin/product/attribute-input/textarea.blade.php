<?php
/**
 * @var CoasterCommerce\Core\Model\Product\Attribute $attribute
 * @var array $meta
 * @var Illuminate\Support\ViewErrorBag $errors
 * @var mixed $value
 */
$lengthGuideArray = array_key_exists('length-guide', $meta) ? explode(',', $meta['length-guide']) : [];
?>

<div class="form-group row">
    <label class="col-sm-3 col-form-label" for="{{ $attribute->id() . $attribute->code }}">
        {{ $attribute->name }}
    </label>
    <div class="col-sm-9">
        <textarea
                id="{{ $attribute->id() . $attribute->code }}"
                name="{{ $attribute->fieldName() }}"
                class="form-control {!! $errors->has($attribute->fieldKey()) ? 'is-invalid' : '' !!}{{ $lengthGuideArray ? ' length-guide' : '' }}"
                @if ($lengthGuideArray)
                data-min="{{ $lengthGuideArray[0] }}"
                data-max="{{ $lengthGuideArray[1] }}"
                @endif
                @if (isset($disabled) && $disabled)
                disabled
                @endif
        >{{ old($attribute->fieldKey(), $value) }}</textarea>
        @if ($errorMessage = $errors->first($attribute->fieldKey()))
            <small class="form-text text-danger">{{ $errorMessage }}</small>
        @elseif (isset($note))
            <small class="form-text text-muted">&raquo; {{ $note }}</small>
        @endif
    </div>
</div>


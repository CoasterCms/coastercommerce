<?php
/**
 * @var CoasterCommerce\Core\Model\Product\Attribute $attribute
 * @var Collective\Html\FormBuilder $formBuilder
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

        {!! $formBuilder->file($attribute->fieldName()) !!}  {!! !is_null($value) ? 'Current File: ' . $value : '' !!}

        @if ($errorMessage = $errors->first($attribute->fieldKey()))
            <small class="form-text text-danger">{{ $errorMessage }}</small>
        @elseif (isset($note))
            <small class="form-text text-muted">&raquo; {{ $note }}</small>
        @endif
    </div>
</div>


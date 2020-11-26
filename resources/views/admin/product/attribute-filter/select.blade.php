<?php
/**
 * @var CoasterCommerce\Core\Model\Product\Attribute $attribute
 * @var array $options
 */
?>

<label for="filter{{ $attribute->code }}">
    {{ $attribute->name }}
</label>
<select
        id="filter{{ $attribute->code }}"
        name="{{ $attribute->fieldName() }}"
        class="form-control"
>
    <option value="">-</option>
    @foreach($options as $optionValue => $optionName)
        <option value="{{ $optionValue }}"{{ $filterState === $optionValue ? ' selected' : '' }}>{{ $optionName }}</option>
    @endforeach
</select>
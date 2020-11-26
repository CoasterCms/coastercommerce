<?php
/**
 * @var CoasterCommerce\Core\Model\Product\Attribute $attribute
 * @var mixed $value
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
    <option value="0"{{ $filterState === '0' ? ' selected' : '' }}>No</option>
    <option value="1"{{ $filterState === '1' ? ' selected' : '' }}>Yes</option>
</select>
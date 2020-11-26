<?php
/**
 * @var CoasterCommerce\Core\Model\Product\Attribute $attribute
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
    <option value="1"{{ $filterState === '1' ? ' selected' : '' }}>In Stock</option>
    <option value="2"{{ $filterState === '2' ? ' selected' : '' }}>Partial Stock</option>
    <option value="0"{{ $filterState === '0' ? ' selected' : '' }}>Out of Stock</option>
</select>
<?php
/**
 * @var CoasterCommerce\Core\Model\Product\Attribute $attribute
 * @var mixed $value
 */
?>

<label for="filter{{ $attribute->code }}">
    {{ $attribute->name }}
</label>
<input
        type="text"
        id="filter{{ $attribute->code }}"
        name="{{ $attribute->fieldName() }}"
        value="{{ $filterState }}"
        class="form-control"
/>
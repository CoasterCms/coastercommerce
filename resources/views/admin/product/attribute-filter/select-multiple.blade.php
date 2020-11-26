<?php
/**
 * @var CoasterCommerce\Core\Model\Product\Attribute $attribute
 * @var array $options
 */
$filterState = $filterState ?: [];
?>

<label for="filter{{ $attribute->code }}">
    {{ $attribute->name }}
</label>
<select
        id="filter{{ $attribute->code }}"
        name="{{ $attribute->fieldName() }}[]"
        class="select2 form-control"
        multiple
>
    @foreach($options as $optionValue => $optionName)
        <option value="{{ $optionValue }}"{{ in_array($optionValue, $filterState) ? ' selected' : '' }}>{{ $optionName }}</option>
    @endforeach
</select>
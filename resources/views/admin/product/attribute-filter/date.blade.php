<?php
/**
 * @var CoasterCommerce\Core\Model\Product\Attribute $attribute
 */
?>

<label>
    {{ $attribute->name }}
</label>
<div class="row">
    <div class="col-6">
        <div class="input-group">
            <div class="input-group-prepend">
                <label class="input-group-text" for="filterfrom{{ $attribute->code }}">From</label>
            </div>
        <input
                type="text"
                id="filterfrom{{ $attribute->code }}"
                name="{{ $attribute->fieldName() }}[from]"
                value="{{ $filterState ? $filterState['from'] : '' }}"
                class="form-control datetime"
        />
        </div>
    </div>
    <div class="col-6">
        <div class="input-group">
            <div class="input-group-prepend">
                <label class="input-group-text" for="filterto{{ $attribute->code }}">To</label>
            </div>
        <input
                type="text"
                id="filterto{{ $attribute->code }}"
                name="{{ $attribute->fieldName() }}[to]"
                value="{{ $filterState ? $filterState['to'] : '' }}"
                class="form-control datetime"
        />
        </div>
    </div>
</div>
<?php
/**
 * @var \Collective\Html\FormBuilder $formBuilder
 * @var \CoasterCommerce\Core\Model\Order\Shipping\TableRate $method
 */
use CoasterCommerce\Core\Renderer\Admin\Attribute;
?>

{!! (new Attribute('condition', 'select', 'Condition'))->key($method->code)->renderInput($method->getCustomField('condition'), ['options' => ['Weight' => 'Weight', 'subtotal' => 'Subtotal']]) !!}

<div class="form-group row">
    <label class="col-sm-3 col-form-label" for="table_rate_new_rates">
        Download Rates
    </label>
    <div class="col-sm-9">
        <a href="{!! route('coaster-commerce.admin.system.shipping.table-rates', ['method' => $method->code]) !!}" target="_blank">Download CSV</a>
    </div>
</div>

{!! (new Attribute('new_rates', 'file-standard', 'Upload New Rates'))->key($method->code)->renderInput() !!}

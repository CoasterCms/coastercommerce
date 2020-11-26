<?php
/**
 * @var \Collective\Html\FormBuilder $formBuilder
 * @var \CoasterCommerce\Core\Model\Order\Shipping\FlatRate $method
 */
use CoasterCommerce\Core\Renderer\Admin\Attribute;
?>

{!! (new Attribute('fixed_rate', 'text', 'Fixed Price'))->key($method->code)->renderInput($method->getCustomField('fixed_rate')) !!}
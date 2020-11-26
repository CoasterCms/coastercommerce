<?php
/**
 * @var \Collective\Html\FormBuilder $formBuilder
 * @var \CoasterCommerce\Core\Model\Order\Shipping\ClickCollect $method
 */
use CoasterCommerce\Core\Renderer\Admin\Attribute;
?>

{!! (new Attribute('collect_payment_only', 'switch', 'Collect Payment Only'))->key($method->code)->renderInput($method->getCustomField('collect_payment_only')) !!}
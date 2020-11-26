<?php
/**
 * @var \Collective\Html\FormBuilder $formBuilder
 * @var \CoasterCommerce\Core\Model\Order\Payment\Stripe $method
 */
use CoasterCommerce\Core\Renderer\Admin\Attribute;
?>

{!! (new Attribute('mode', 'select', 'Mode'))->key($method->code)->renderInput($method->getCustomField('mode'), ['options' => ['live' => 'Live', 'test' => 'Test']]) !!}
<div id="stripeLive" style="display: none">
    {!! (new Attribute('pk_live', 'text', 'Publishable Key'))->key($method->code)->renderInput($method->getCustomField('pk_live')) !!}
    {!! (new Attribute('sk_live', 'text', 'Secret Key'))->key($method->code)->renderInput($method->getCustomField('sk_live')) !!}
</div>
<div id="stripeTest" style="display: none">
    {!! (new Attribute('pk_test', 'text', 'Publishable Key (Test)'))->key($method->code)->renderInput($method->getCustomField('pk_test')) !!}
    {!! (new Attribute('sk_test', 'text', 'Secret Key (Test)'))->key($method->code)->renderInput($method->getCustomField('sk_test')) !!}
</div>

@section('scripts')
    <script>
        $(document).ready(function ()  {
            $("select[name={{ $method->code }}\\[mode\\]]").change(function () {
                if ($(this).val() === 'live') {
                    $('#stripeLive').show();
                    $('#stripeTest').hide();
                } else {
                    $('#stripeLive').hide();
                    $('#stripeTest').show();
                }
            }).trigger('change');
        });
    </script>
@append
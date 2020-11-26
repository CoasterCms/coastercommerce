<?php
/**
 * @var \Collective\Html\FormBuilder $formBuilder
 * @var \CoasterCommerce\Core\Model\Order\Payment\PayPal $method
 */
use CoasterCommerce\Core\Renderer\Admin\Attribute;
?>

{!! (new Attribute('mode', 'select', 'Mode'))->key($method->code)->renderInput($method->getCustomField('mode'), ['options' => ['live' => 'Live', 'test' => 'Test (Sandbox)']]) !!}
<div id="ppLive" style="display: none">
    {!! (new Attribute('id_live', 'text', 'Client Id'))->key($method->code)->renderInput($method->getCustomField('id_live')) !!}
    {!! (new Attribute('secret_live', 'text', 'Client Secret'))->key($method->code)->renderInput($method->getCustomField('secret_live')) !!}
</div>
<div id="ppTest" style="display: none">
    {!! (new Attribute('id_sandbox', 'text', 'Client Id (Sandbox)'))->key($method->code)->renderInput($method->getCustomField('id_sandbox')) !!}
    {!! (new Attribute('secret_sandbox', 'text', 'Client Secret (Sandbox)'))->key($method->code)->renderInput($method->getCustomField('secret_sandbox')) !!}
</div>

@section('scripts')
    <script>
        $(document).ready(function ()  {
            $("select[name={{ $method->code }}\\[mode\\]]").change(function () {
                if ($(this).val() === 'live') {
                    $('#ppLive').show();
                    $('#ppTest').hide();
                } else {
                    $('#ppLive').hide();
                    $('#ppTest').show();
                }
            }).trigger('change');
        });
    </script>
@append
<?php
/**
 * @var \Collective\Html\FormBuilder $formBuilder
 * @var \CoasterCommerce\Core\Model\Order\Payment\SagePay $method
 */
use CoasterCommerce\Core\Renderer\Admin\Attribute;
?>

{!! (new Attribute('vendor', 'text', 'Vendor Name'))->key($method->code)->renderInput($method->getCustomField('vendor')) !!}
{!! (new Attribute('mode', 'select', 'Mode'))->key($method->code)->renderInput($method->getCustomField('mode'), ['options' => ['live' => 'Live', 'test' => 'Test']]) !!}
<div id="sagePayLive" style="display: none">
    {!! (new Attribute('key_live', 'password', 'Encryption Key'))->key($method->code)->renderInput($method->getCustomField('key_live')) !!}
</div>
<div id="sagePayTest" style="display: none">
    {!! (new Attribute('key_test', 'text', 'Encryption Key (Test)'))->key($method->code)->renderInput($method->getCustomField('key_test')) !!}
</div>

{!! (new Attribute('email_bcc', 'text', 'Email Bcc (colon separated)'))->key($method->code)->renderInput($method->getCustomField('email_bcc')) !!}

{!! (new Attribute('api_user', 'text', 'API User (unused)'))->key($method->code)->renderInput($method->getCustomField('api_user')) !!}
{!! (new Attribute('api_pass', 'password', 'API Pass (unused)'))->key($method->code)->renderInput($method->getCustomField('api_pass')) !!}

@section('scripts')
    <script>
        $(document).ready(function ()  {
            $("select[name={{ $method->code }}\\[mode\\]]").change(function () {
                if ($(this).val() === 'live') {
                    $('#sagePayLive').show();
                    $('#sagePayTest').hide();
                } else {
                    $('#sagePayLive').hide();
                    $('#sagePayTest').show();
                }
            }).trigger('change');
        });
    </script>
@append
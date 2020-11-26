<?php
/** @var \CoasterCommerce\Core\Model\Order\Payment\SagePay\FormAPI $formAPI */
?>

<html>

    <head>
        <title>SagePay Redirect</title>
    </head>

    <body>
        <form method="POST" id="SagePayForm" action="{{ $formAPI->getFormAction() }}">
            <input type="hidden" name="VPSProtocol" value="{{ $formAPI->getVPSProtocol() }}">
            <input type="hidden" name="TxType" value="{{ $formAPI->getTxType() }}">
            <input type="hidden" name="Vendor" value="{{ $formAPI->getVendor() }}">
            <input type="hidden" name="Crypt" value="{{ $formAPI->getCrypt() }}">
            <input type="submit" value="Redirecting to SagePay ....">
        </form>

        <script>
            document.getElementById("SagePayForm").submit();
        </script>
    </body>

</html>
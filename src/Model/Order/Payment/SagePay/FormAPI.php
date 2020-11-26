<?php
namespace CoasterCommerce\Core\Model\Order\Payment\SagePay;

use Exception;

class FormAPI
{

    /**
     * Available Modes
     */
    const LIVE_MODE = 'live';
    const TEST_MODE = 'test';

    /**
     * @var string
     */
    protected $_vendor;

    /**
     * @var string
     */
    protected $_txType;

    /**
     * @var string
     */
    protected $_encryptionPassword;

    /**
     * @var string
     */
    protected $_mode;

    /**
     * @var string
     */
    protected $_vpsProtocol;

    /**
     * @var array
     */
    protected $_cryptData;

    /**
     * SagePay FormAPI constructor.
     * @param string $encryptionPassword
     * @param string $mode
     */
    public function __construct($encryptionPassword, $mode = null)
    {
        $this->_encryptionPassword = $encryptionPassword;
        $this->_mode = $mode ?: static::LIVE_MODE;
        $this->_txType = 'PAYMENT';
        $this->_vpsProtocol = '3.00';
        $this->_cryptData = [];
    }

    /**
     * @param string $vendor
     * @return static
     */
    public function setVendor($vendor)
    {
        $this->_vendor = $vendor;
        return $this;
    }

    /**
     * @return string
     */
    public function getFormAction()
    {
        return $this->_mode == static::LIVE_MODE ?
            'https://live.sagepay.com/gateway/service/vspform-register.vsp' :
            'https://test.sagepay.com/gateway/service/vspform-register.vsp';
    }

    /**
     * @return string
     */
    public function getVendor()
    {
        return $this->_vendor;
    }

    /**
     * @return string
     */
    public function getTxType()
    {
        return $this->_txType;
    }

    /**
     * @return string
     */
    public function getVPSProtocol()
    {
        return $this->_vpsProtocol;
    }

    /**
     * @return string
     */
    public function getMode()
    {
        return $this->_mode;
    }

    /**
     * Get crypt value with quick key check
     * @return string
     * @throws Exception
     */
    public function getCrypt()
    {
        if ($missingKeys = array_diff_key(array_flip($this->_requiredCryptData()), $this->_cryptData)) {
            throw new Exception('Missing crypt parameters: ' . implode(', ', array_flip($missingKeys)));
        }
        $cryptArray = [];
        foreach ($this->_cryptData as $field => $value) {
            if (!is_null($value)) {
                $cryptArray[] = $field . '=' . $value;
            }
        }
        return $this->encryptAndEncode(implode('&', $cryptArray));
    }

    /**
     * @param string $cryptValue
     * @return array
     */
    public function decodeCrypt($cryptValue)
    {
        $decodedString = $this->decodeAndDecrypt($cryptValue);
        parse_str($decodedString, $sagePayResponse);
        return $sagePayResponse;
    }

    /**
     * @param array $cryptData
     */
    public function setCryptArray($cryptData)
    {
        $this->_cryptData = $cryptData;
    }

    /**
     * @return array
     */
    protected function _requiredCryptData()
    {
        return [
            'VendorTxCode',
            'Amount',
            'Currency',
            'Description',
            'SuccessURL',
            'FailureURL',
            'BillingSurname',
            'BillingFirstnames',
            'BillingAddress1',
            'BillingCity',
            'BillingPostCode',
            'BillingCountry',
            'DeliverySurname',
            'DeliveryFirstnames',
            'DeliveryAddress1',
            'DeliveryCity',
            'DeliveryPostCode',
            'DeliveryCountry',
        ];
    }

    /**
     * @param string $strIn
     * @return string
     */
    protected function encryptAndEncode($strIn)
    {
        return "@" . bin2hex(openssl_encrypt($strIn, 'AES-128-CBC', $this->_encryptionPassword, OPENSSL_RAW_DATA, $this->_encryptionPassword));
    }

    /**
     * @param string $strIn
     * @return string
     */
    protected function decodeAndDecrypt($strIn)
    {
        $strIn = substr($strIn, 1);
        $strIn = pack('H*', $strIn);
        return openssl_decrypt($strIn, 'AES-128-CBC', $this->_encryptionPassword, OPENSSL_RAW_DATA, $this->_encryptionPassword);
    }

}
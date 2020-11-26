<?php

namespace CoasterCommerce\Core\Model\Customer\Auth;

/**
 * Supports md5 & sha256 or mixture of both
 * https://devdocs.magento.com/guides/v2.4/config-guide/secy/hashing.html
 * Class Magento
 * @package CoasterCommerce\Core\Model\Customer\Auth
 */
class Magento
{

    /**
     * @var string
     */
    protected $_password;

    /**
     * @var string
     */
    protected $_hash;

    /**
     * @var array
     */
    protected $_hashStrategies;

    /**
     * Magento constructor.
     * @param string $password
     * @param string $hash
     */
    public function __construct($password, $hash)
    {
        $this->_password = $password;
        $this->_hash = $hash;
        $this->_hashStrategies = [
            '0' => '_md5',
            '1' => '_sha256'
        ];
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        $parts = explode(':', $this->_hash);
        if (count($parts) < 3) {
            return false; // not a magento hash
        }
        [$storedHash, $salt] = $parts;
        $versions = array_slice($parts, 2);
        $computedHash = $this->_password;
        foreach ($versions as $version) {
            $hashStrategy = $this->_hashStrategies[$version];
            $computedHash = $this->$hashStrategy($computedHash, $salt);
        }
        return $storedHash === $computedHash;
    }

    /**
     * @param string $hash
     * @param string $salt
     * @return string
     */
    protected function _md5($hash, $salt)
    {
        return md5($salt . $hash);
    }

    /**
     * @param string $hash
     * @param string $salt
     * @return string
     */
    protected function _sha256($hash, $salt)
    {
        return hash('sha256', $salt . $hash);
    }

}

<?php

namespace horstoeko\slimapp\crypt;

use \MiladRahimi\PhpCrypt\Symmetric;

/**
 * Encrypt with MiladRahimi\PhpCrypt\Symmetric (uses openssl)
 */
class SlimAppEncryptionOpenSslExt implements SlimAppEncryptionInterface
{
    /**
     * Symetric instance
     *
     * @var \MiladRahimi\PhpCrypt\Symmetric
     */
    private $_symetric = null;

    /**
     * Encryption method
     *
     * @var string
     */
    private $_method = "aes-256-cbc";

    /**
     * Secret key
     *
     * @var string
     */
    private $_key = "2Tn)O=AQ%_&>/YZR@.PY@k@KG";

    /**
     * Flag, that the component is initialized
     *
     * @var boolean
     */
    private $_initialized = false;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->_setup();
    }

    /**
     * Get the internal name of the engine
     *
     * @return string
     */
    public function getname()
    {
        return "opensslext";
    }

    /**
     * Encrypt data
     *
     * @param string $plaintext
     * @return string
     */
    public function encrypt($plaintext)
    {
        if (!$this->isInstalled()) {
            return $plaintext;
        }

        return $this->_getSymetric()->encrypt($plaintext);
    }

    /**
     * Decrypt data
     *
     * @param string $encrypted
     * @return string
     */
    public function decrypt($encrypted)
    {
        if (!$this->isInstalled()) {
            return $encrypted;
        }

        return $this->_getSymetric()->decrypt($encrypted);
    }

    /**
     * Return if correct installed
     *
     * @return boolean
     */
    public function isInstalled()
    {
        return $this->_initialized && $this->_opensslInstalled();
    }

    /**
     * Internal setup
     *
     * @return string
     */
    private function _setup()
    {
        $this->_initialized = true;
    }

    /**
     * Initialize the symetric class
     *
     * @return Symetric
     */
    private function _getSymetric()
    {
        if ($this->_symetric == null) {
            $this->_symetric = new Symmetric($this->_key, $this->_method);
        }

        return $this->_symetric;
    }

    /**
     * Check if openssl is installed
     *
     * @return bool
     */
    private function _opensslInstalled()
    {
        return extension_loaded('openssl');
    }

    /**
     * Magic getter
     *
     * @param string $varname
     * @return mixed
     */
    public function __get($varname)
    {
        switch (strtolower($varname)) {
            case 'method':
                return $this->_method;
            case 'key':
                return $this->_key;
        }
    }

    /**
     * Magic setter
     *
     * @param string $varname
     * @param mixed $varvalue
     */
    public function __set($varname, $varvalue)
    {
        switch (strtolower($varname)) {
            case 'method':
                $this->_method = $varvalue;
            case 'key':
                $this->_key = $varvalue;
        }
    }

    /**
     * Magic isset()
     *
     * @param string $varname
     * @return bool
     */
    public function __isset($varname)
    {
        switch (strtolower($varname)) {
            case 'method':
                return isset($this->_method);
            case 'key':
                return isset($this->_key);
        }
    }
}

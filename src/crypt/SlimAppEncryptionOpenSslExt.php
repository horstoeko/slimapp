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
    private $symetric = null;

    /**
     * Encryption method
     *
     * @var string
     */
    private $method = "aes-256-cbc";

    /**
     * Secret key
     *
     * @var string
     */
    private $key = "2Tn)O=AQ%_&>/YZR@.PY@k@KG";

    /**
     * Flag, that the component is initialized
     *
     * @var boolean
     */
    private $initialized = false;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->setup();
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

        return $this->getSymetric()->encrypt($plaintext);
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

        return $this->getSymetric()->decrypt($encrypted);
    }

    /**
     * Return if correct installed
     *
     * @return boolean
     */
    public function isInstalled()
    {
        return $this->initialized && $this->opensslInstalled();
    }

    /**
     * Internal setup
     *
     * @return string
     */
    private function setup()
    {
        $this->initialized = true;
    }

    /**
     * Initialize the symetric class
     *
     * @return Symetric
     */
    private function getSymetric()
    {
        if ($this->symetric == null) {
            $this->symetric = new Symmetric($this->key, $this->method);
        }

        return $this->symetric;
    }

    /**
     * Check if openssl is installed
     *
     * @return bool
     */
    private function opensslInstalled()
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
                return $this->method;
            case 'key':
                return $this->key;
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
                $this->method = $varvalue;
                break;
            case 'key':
                $this->key = $varvalue;
                break;
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
                return isset($this->method);
            case 'key':
                return isset($this->key);
        }
    }
}

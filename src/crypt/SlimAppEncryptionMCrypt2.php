<?php

namespace horstoeko\slimapp\crypt;

/**
 * MCrypt2 implementation
 * See: https://www.warpconduit.net/2013/04/14/highly-secure-data-encryption-decryption-made-easy-with-php-mcrypt-rijndael-256-and-cbc/
 */
class SlimAppEncryptionMCrypt2 implements SlimAppEncryptionInterface
{

    private $_key;
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
        return "mcrypt2";
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
        $encrypt = serialize($plaintext);
        $iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC), MCRYPT_DEV_URANDOM);
        $key = pack('H*', $this->_key);
        $mac = hash_hmac('sha256', $encrypt, substr(bin2hex($key), -32));
        $passcrypt = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $key, $encrypt . $mac, MCRYPT_MODE_CBC, $iv);
        $encoded = base64_encode($passcrypt) . '|' . base64_encode($iv);
        return $encoded;
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
        $decrypt = explode('|', $encrypted . '|');
        $decoded = base64_decode($decrypt[0]);
        $iv = base64_decode($decrypt[1]);
        if (strlen($iv) !== mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC)) {
            return $encrypted;
        }
        $key = pack('H*', $this->_key);
        $decrypted = trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key, $decoded, MCRYPT_MODE_CBC, $iv));
        $mac = substr($decrypted, -64);
        $decrypted = substr($decrypted, 0, -64);
        $calcmac = hash_hmac('sha256', $decrypted, substr(bin2hex($key), -32));
        if ($calcmac !== $mac) {
            return $encrypted;
        }
        $decrypted = unserialize($decrypted);
        return $decrypted;
    }

    /**
     * Return if correct installed
     *
     * @return boolean
     */
    public function isInstalled()
    {
        return $this->_initialized && $this->_mcryptInstalled();
    }

    /**
     * Internal setup
     *
     * @return string
     */
    private function _setup()
    {
        $this->_key = "dd3ccaf10fb759ef9c434b933c8324c335dbad806a88c26db9b3ff327e73e675";
        $this->_initialized = true;
    }

    /**
     * Check is installed
     *
     * @return bool
     */
    private function _mcryptInstalled()
    {
        return
        function_exists("mcrypt_encrypt") &&
        function_exists("mcrypt_create_iv") &&
        function_exists("mcrypt_get_iv_size") &&
        function_exists("mcrypt_decrypt") &&
        function_exists("pack") &&
        function_exists("hash_hmac") &&
        function_exists("base64_encode") &&
        function_exists("base64_decode") &&
        function_exists("serialize") &&
        function_exists("unserialize") &&
        function_exists("explode") &&
        function_exists("substr") &&
        function_exists("bin2hex");
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
        switch ($varname) {
            case 'key':
                $this->_key = $varvalue;
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
        switch ($varname) {
            case 'key':
                return isset($this->_key);
        }
    }

}

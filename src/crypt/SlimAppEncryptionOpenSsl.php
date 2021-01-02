<?php

namespace horstoeko\slimapp\crypt;

use horstoeko\stringmanagement\PathUtils;

/**
 * Encrypt with OpenSSL
 */
class SlimAppEncryptionOpenSsl implements SlimAppEncryptionInterface
{
    /**
     * @var string
     */
    const PRIVATE_KEY_FILENAME = "private.key";

    /**
     * @var string
     */
    const PUBLIC_KEY_FILENAME = "public.key";

    /**
     * @var boolean
     */
    private $_initialized = false;

    /**
     * @var string
     */
    private $_tempdirectory = __DIR__;

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
        return "openssl";
    }

    /**
     * Generate keys and store them as files
     *
     * @return void
     */
    public function generateKeys()
    {
        if (!$this->isInstalled()) {
            return false;
        }
        if ($this->_keyExists()) {
            return true;
        }

        $privateKey = openssl_pkey_new(array(
            'digest_alg' => 'sha512',
            'private_key_bits' => 4096,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ));

        $privkeyFilename = $this->_getPrivateKeyFilename();
        $publickeyFilename = $this->_getPublicKeyFilename();

        openssl_pkey_export_to_file($privateKey, $privkeyFilename);
        $a_key = openssl_pkey_get_details($privateKey);
        file_put_contents($publickeyFilename, $a_key['key']);
        openssl_free_key($privateKey);

        if ($this->_keyExists()) {
            return true;
        }

        return false;
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
        if (!$this->generateKeys()) {
            return $plaintext;
        }

        $plaintext = gzcompress($plaintext);

        $publicKey = openssl_pkey_get_public(file_get_contents($this->_getPublicKeyFilename()));

        if ($publicKey === false) {
            return $plaintext;
        }

        $a_key = openssl_pkey_get_details($publicKey);
        $chunkSize = ceil($a_key['bits'] / 8) - 11;
        $output = '';

        while ($plaintext) {
            $chunk = substr($plaintext, 0, $chunkSize);
            $plaintext = substr($plaintext, $chunkSize);
            $encrypted = '';
            if (!openssl_public_encrypt($chunk, $encrypted, $publicKey)) {
                return $plaintext;
            }
            $output .= $encrypted;
        }

        openssl_free_key($publicKey);

        $output = base64_encode($output);

        return $output;
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
        if (!$this->generateKeys()) {
            return $encrypted;
        }

        $encrypted = base64_decode($encrypted);

        $privateKey = openssl_pkey_get_private(file_get_contents($this->_getPrivateKeyFilename()));

        if ($privateKey === false) {
            return $encrypted;
        }

        $a_key = openssl_pkey_get_details($privateKey);
        $chunkSize = ceil($a_key['bits'] / 8);
        $output = '';

        while ($encrypted) {
            $chunk = substr($encrypted, 0, $chunkSize);
            $encrypted = substr($encrypted, $chunkSize);
            $decrypted = '';
            if (!openssl_private_decrypt($chunk, $decrypted, $privateKey)) {
                return $encrypted;
            }
            $output .= $decrypted;
        }

        openssl_free_key($privateKey);

        $output = gzuncompress($output);

        return $output;
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
        $this->_tempdirectory = __DIR__;
        $this->_initialized = true;
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
     * Get filename for private key
     *
     * @return string
     */
    private function _getPrivateKeyFilename()
    {
        return PathUtils::combinePathWithFile($this->_tempdirectory, self::PRIVATE_KEY_FILENAME);
    }

    /**
     * Get filename for public key
     *
     * @return string
     */
    private function _getPublicKeyFilename()
    {
        return PathUtils::combinePathWithFile($this->_tempdirectory, self::PUBLIC_KEY_FILENAME);
    }

    /**
     * Check if a key file already exists
     *
     * @return bool
     */
    private function _keyExists()
    {
        return file_exists($this->_getPrivateKeyFilename()) && file_exists($this->_getPublicKeyFilename());
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
            case 'privatekeyfile':
                return $this->_getPrivateKeyFilename();
            case 'publickeyfile':
                return $this->_getPublicKeyFilename();
            case 'keyexists':
                return $this->_keyExists();
            case 'tempdirectory':
                return $this->_tempdirectory;
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
            case 'tempdirectory':
                $this->_tempdirectory = $varvalue;
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
            case 'privatekeyfile':
                return true;
            case 'publickeyfile':
                return true;
            case 'keyexists':
                return true;
            case 'tempdirectory':
                return true;
        }
    }
}

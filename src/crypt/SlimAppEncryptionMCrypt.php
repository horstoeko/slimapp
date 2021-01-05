<?php

namespace horstoeko\slimapp\crypt;

class SlimAppEncryptionMCrypt implements SlimAppEncryptionInterface
{

    private $key;
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
        return "mcrypt";
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
        return rtrim(
            base64_encode(
                mcrypt_encrypt(
                    MCRYPT_RIJNDAEL_256,
                    $this->key,
                    $plaintext,
                    MCRYPT_MODE_ECB,
                    mcrypt_create_iv(
                        mcrypt_get_iv_size(
                            MCRYPT_RIJNDAEL_256,
                            MCRYPT_MODE_ECB
                        ),
                        MCRYPT_RAND
                    )
                )
            ),
            "\0"
        );
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

        return rtrim(
            mcrypt_decrypt(
                MCRYPT_RIJNDAEL_256,
                $this->key,
                base64_decode($encrypted),
                MCRYPT_MODE_ECB,
                mcrypt_create_iv(
                    mcrypt_get_iv_size(
                        MCRYPT_RIJNDAEL_256,
                        MCRYPT_MODE_ECB
                    ),
                    MCRYPT_RAND
                )
            ),
            "\0"
        );
    }

    /**
     * Return if correct installed
     *
     * @return boolean
     */
    public function isInstalled()
    {
        return $this->initialized && $this->mcryptInstalled();
    }

    /**
     * Internal setup
     *
     * @return void
     */
    private function setup()
    {
        $this->key = "dd3ccaf10fb759ef9c434b933c8324c335dbad806a88c26db9b3ff327e73e675";
        $this->initialized = true;
    }

    /**
     * Check if installed
     *
     * @return void
     */
    private function mcryptInstalled()
    {
        return function_exists("mcrypt_encrypt") &&
            function_exists("mcrypt_create_iv") &&
            function_exists("mcrypt_get_iv_size") &&
            function_exists("mcrypt_decrypt");
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
            case 'key':
                return isset($this->key);
        }
    }
}

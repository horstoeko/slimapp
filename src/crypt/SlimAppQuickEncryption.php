<?php

namespace horstoeko\slimapp\crypt;

class SlimAppQuickEncryption
{

    /**
     * Quick encryption with MCrypt2
     *
     * @param string $plaintext
     * @return string
     */
    public static function encryptMcrypt2($plaintext)
    {
        return self::getEncryptionManager("mcrypt2")->encrypt($plaintext);
    }

    /**
     * Quick decryption with MCrypt2
     *
     * @param string $encrypted
     * @return string
     */
    public static function decryptMcrypt2($encrypted)
    {
        return self::getEncryptionManager("mcrypt2")->decrypt($encrypted);
    }

    /**
     * Compare a string encrypted with MCrypt2
     *
     * @param string $encryptedstring
     * @param string $stringtocomparewith
     * @return bool
     */
    public function compareMcrypt2($encryptedstring, $stringtocomparewith)
    {
        return self::getEncryptionManager("mcrypt2")->compare($encryptedstring, $stringtocomparewith);
    }

    /**
     * Quick encryption with OpenSsl
     *
     * @param string $plaintext
     * @return string
     */
    public static function encryptOpenSsl($plaintext)
    {
        return self::getEncryptionManager("openssl")->encrypt($plaintext);
    }

    /**
     * Quick decryption with OpenSsl
     *
     * @param string $encrypted
     * @return string
     */
    public static function decryptOpenSsl($encrypted)
    {
        return self::getEncryptionManager("openssl")->decrypt($encrypted);
    }

    /**
     * Compare a string encrypted with OpenSsl
     *
     * @param string $encryptedstring
     * @param string $stringtocomparewith
     * @return bool
     */
    public function compareOpenSsl($encryptedstring, $stringtocomparewith)
    {
        return self::getEncryptionManager("openssl")->compare($encryptedstring, $stringtocomparewith);
    }

    /**
     * Quick encryption with OpenSslExt
     *
     * @param string $plaintext
     * @return string
     */
    public static function encryptOpenSslExt($plaintext)
    {
        return self::getEncryptionManager("opensslext")->encrypt($plaintext);
    }

    /**
     * Quick decryption with OpenSslExt
     *
     * @param string $encrypted
     * @return string
     */
    public static function decryptOpenSslExt($encrypted)
    {
        return self::getEncryptionManager("opensslext")->decrypt($encrypted);
    }

    /**
     * Compare a string encrypted with OpenSslExt
     *
     * @param string $encryptedstring
     * @param string $stringtocomparewith
     * @return bool
     */
    public function compareOpenSslExt($encryptedstring, $stringtocomparewith)
    {
        return self::getEncryptionManager("opensslext")->compare($encryptedstring, $stringtocomparewith);
    }

    /**
     * Get instance of encryption manager
     *
     * @return SlimAppEncryptionManager
     */
    private static function getEncryptionManager($useEngine)
    {
        $encryptionManager = new SlimAppEncryptionManager();
        $encryptionManager->disableAllEngines();
        $encryptionManager->enableEngine($useEngine);
        return $encryptionManager;
    }
}

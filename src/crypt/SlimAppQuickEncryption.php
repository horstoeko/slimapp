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
    public static function EncryptMcrypt2($plaintext)
    {
        return self::_getEncryptionManager("mcrypt2")->encrypt($plaintext);
    }

    /**
     * Quick decryption with MCrypt2
     *
     * @param string $encrypted
     * @return string
     */
    public static function DecryptMcrypt2($encrypted)
    {
        return self::_getEncryptionManager("mcrypt2")->decrypt($encrypted);
    }

    /**
     * Compare a string encrypted with MCrypt2
     *
     * @param string $encryptedstring
     * @param string $stringtocomparewith
     * @return bool
     */
    public function CompareMcrypt2($encryptedstring, $stringtocomparewith)
    {
        return self::_getEncryptionManager("mcrypt2")->compare($encryptedstring, $stringtocomparewith);
    }

    /**
     * Quick encryption with OpenSsl
     *
     * @param string $plaintext
     * @return string
     */
    public static function EncryptOpenSsl($plaintext)
    {
        return self::_getEncryptionManager("openssl")->encrypt($plaintext);
    }

    /**
     * Quick decryption with OpenSsl
     *
     * @param string $encrypted
     * @return string
     */
    public static function DecryptOpenSsl($encrypted)
    {
        return self::_getEncryptionManager("openssl")->decrypt($encrypted);
    }

    /**
     * Compare a string encrypted with OpenSsl
     *
     * @param string $encryptedstring
     * @param string $stringtocomparewith
     * @return bool
     */
    public function CompareOpenSsl($encryptedstring, $stringtocomparewith)
    {
        return self::_getEncryptionManager("openssl")->compare($encryptedstring, $stringtocomparewith);
    }

    /**
     * Quick encryption with OpenSslExt
     *
     * @param string $plaintext
     * @return string
     */
    public static function EncryptOpenSslExt($plaintext)
    {
        return self::_getEncryptionManager("opensslext")->encrypt($plaintext);
    }

    /**
     * Quick decryption with OpenSslExt
     *
     * @param string $encrypted
     * @return string
     */
    public static function DecryptOpenSslExt($encrypted)
    {
        return self::_getEncryptionManager("opensslext")->decrypt($encrypted);
    }

    /**
     * Compare a string encrypted with OpenSslExt
     *
     * @param string $encryptedstring
     * @param string $stringtocomparewith
     * @return bool
     */
    public function CompareOpenSslExt($encryptedstring, $stringtocomparewith)
    {
        return self::_getEncryptionManager("opensslext")->compare($encryptedstring, $stringtocomparewith);
    }

    /**
     * Get instance of encryption manager
     *
     * @return SlimAppEncryptionManager
     */
    private static function _getEncryptionManager($useEngine)
    {
        $encryptionManager = new SlimAppEncryptionManager();
        $encryptionManager->disableAllEngines();
        $encryptionManager->enableEngine($useEngine);
        return $encryptionManager;
    }
}

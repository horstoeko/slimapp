<?php

namespace horstoeko\slimapp\crypt;

interface SlimAppEncryptionInterface
{
    /**
     * Get the internal name of the engine
     *
     * @return string
     */
    public function getname();

    /**
     * Encrypt data
     *
     * @param string $plaintext
     * @return string
     */
    public function encrypt($plaintext);

    /**
     * Decrypt data
     *
     * @param string $encrypted
     * @return string
     */
    public function decrypt($encrypted);

    /**
     * Check if installed
     *
     * @return void
     */
    public function isInstalled();
}

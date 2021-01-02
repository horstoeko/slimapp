<?php

declare(strict_types=1);

namespace horstoeko\slimapp\eloquent;

use Illuminate\Database\Eloquent\Model as Eloquent;
use horstoeko\slimapp\crypt\SlimAppQuickEncryption;

class ModelWithEncryption extends Eloquent
{
    /**
     * List of encryptable/decryptable columns
     *
     * @var array
     */
    protected $encryptable = [];

    /**
     * @inheritDoc
     */
    public function getAttribute($key)
    {
        $value = parent::getAttribute($key);
        if (in_array($key, $this->encryptable) && $value !== '') {
            $value = $this->decrypt($value);
        }
        return $value;
    }

    /**
     * @inheritDoc
     */
    public function setAttribute($key, $value)
    {
        if (in_array($key, $this->encryptable)) {
            $value = $this->encrypt($value);
        }
        return parent::setAttribute($key, $value);
    }

    /**
     * @inheritDoc
     */
    public function attributesToArray()
    {
        $attributes = parent::attributesToArray();
        foreach ($this->encryptable as $key) {
            if (isset($attributes[$key])) {
                $attributes[$key] = $this->decrypt($attributes[$key]);
            }
        }
        return $attributes;
    }

    /**
     * Decrypt a value
     *
     * @param mixed $value
     * @return mixed
     */
    private function decrypt($value)
    {
        return SlimAppQuickEncryption::DecryptOpenSsl($value);
    }

    /**
     * Encrypt a value
     *
     * @param mixed $value
     * @return mixed
     */
    private function encrypt($value)
    {
        return SlimAppQuickEncryption::EncryptOpenSsl($value);
    }
}

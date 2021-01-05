<?php

namespace horstoeko\slimapp\crypt;

class SlimAppEncryptionManager
{
    private $registeredEngines = [];

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->addEngine(SlimAppEncryptionMCrypt::class);
        $this->addEngine(SlimAppEncryptionMCrypt2::class);
        $this->addEngine(SlimAppEncryptionOpenSslExt::class);
        $this->addEngine(SlimAppEncryptionOpenSsl::class);
    }

    /**
     * Register an encryption engine
     *
     * @param string $name
     * @param string $class
     * @return void
     */
    public function addEngine($class, $enable = true)
    {
        $reflectionClass = new \ReflectionClass($class);

        if (!$reflectionClass->implementsInterface('\horstoeko\slimapp\crypt\SlimAppEncryptionInterface')) {
            return;
        }

        $instance = new $class();
        $name = $instance->getName();

        if (!$this->hasEngine($name)) {
            if ($instance->isInstalled()) {
                $engine = new \stdClass();
                $engine->enabled = $enable;
                $engine->instance = $instance;
                $this->registeredEngines[$name] = $engine;
            }
        }
    }

    /**
     * Remove an encryption engine
     *
     * @param string $name
     * @return void
     */
    public function removeEngine($name)
    {
        if (!$this->hasEngine($name)) {
            return;
        }

        unset($this->registeredEngines[$name]);
    }

    /**
     * Enable an encryption engine
     *
     * @param string $name
     * @return void
     */
    public function enableEngine($name)
    {
        if (!$this->hasEngine($name)) {
            return;
        }

        $engine = $this->registeredEngines[$name];
        $engine->enabled = true;
    }

    /**
     * Disable an encryption engine
     *
     * @param string $name
     * @return void
     */
    public function disableEngine($name)
    {
        if (!$this->hasEngine($name)) {
            return;
        }

        $engine = $this->registeredEngines[$name];
        $engine->enabled = false;
    }

    /**
     * Enable all registered engines
     *
     * @return void
     */
    public function enableAllEngines()
    {
        foreach ($this->registeredEngines as $engineName => $engineObject) {
            $this->enableEngine($engineName);
        }
    }

    /**
     * Disable all registered engines
     *
     * @return void
     */
    public function disableAllEngines()
    {
        foreach ($this->registeredEngines as $engineName => $engineObject) {
            $this->disableEngine($engineName);
        }
    }

    /**
     * Check if an engine with $name is registered
     *
     * @param string $name
     * @return boolean
     */
    public function hasEngine($name)
    {
        return isset($this->registeredEngines[$name]);
    }

    /**
     * Get the instance of the engine
     *
     * @param string $name
     * @return null|object
     */
    public function getEngine($name)
    {
        if (!$this->hasEngine($name)) {
            return null;
        }

        return $this->registeredEngines[$name]->instance;
    }

    /**
     * Encrypt a string
     *
     * @param string $data
     * @return string
     */
    public function encrypt($data)
    {
        foreach ($this->registeredEngines as $registeredEngine) {
            if ($registeredEngine->enabled === false) {
                continue;
            }

            $data = $registeredEngine->instance->encrypt($data);

            break;
        }

        return $data;
    }

    /**
     * Decrypt a string
     *
     * @param string $data
     * @return string
     */
    public function decrypt($data)
    {
        foreach ($this->registeredEngines as $registeredEngine) {
            if ($registeredEngine->enabled === false) {
                continue;
            }

            $data = $registeredEngine->instance->decrypt($data);

            break;
        }

        return $data;
    }

    /**
     * Compare a string
     *
     * @param string $encryptedstring
     * @param string $stringtocomparewith
     * @return bool
     */
    public function compare($encryptedstring, $stringtocomparewith)
    {
        return (strcmp($this->decrypt($encryptedstring), $stringtocomparewith) === 0);
    }

    /**
     * Magic getter
     *
     * @param string $varname
     * @return mixed
     */
    public function __get($varname)
    {
        list($engineName, $property) = $this->splitVarname($varname);

        $engine = $this->getEngine($engineName);

        if ($engine !== null) {
            return $engine->$property;
        }

        return null;
    }

    /**
     * Magic setter
     *
     * @param string $varname
     * @param mixed $varvalue
     */
    public function __set($varname, $varvalue)
    {
        list($engineName, $property) = $this->splitVarname($varname);

        $engine = $this->getEngine($engineName);

        if ($engine !== null) {
            $engine->$property = $varvalue;
        }
    }

    /**
     * Magic isset
     *
     * @param string $varname
     * @return boolean
     */
    public function __isset($varname)
    {
        list($engineName, $property) = $this->splitVarname($varname);

        $engine = $this->getEngine($engineName);

        if ($engine !== null) {
            return isset($engine->$property);
        }

        return false;
    }

    /**
     * Split name, the form is enginename_property
     *
     * @param [type] $varname
     * @return void
     */
    private function splitVarname($varname)
    {
        return explode("_", $varname);
    }
}

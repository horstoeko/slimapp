<?php

namespace horstoeko\slimapp\twig;

use \Twig\Cache\CacheInterface as TwigCacheInterface;

class SlimAppTwigApcuCache implements TwigCacheInterface
{
    /**
     * @var string
     */
    protected $namespace = "";

    /**
     * Constructor
     *
     * @param string $namespace
     */
    public function __construct($namespace = "")
    {
        $this->namespace = $namespace;
    }

    /**
     * @inheritDoc
     */
    public function generateKey(string $name, string $className): string
    {
        if ($this->namespace) {
            $key = "{$this->namespace}:$name;$className";
        } else {
            $key = "$name;$className";
        }
        $key = "TWIG_CACHE;" . hash_hmac('sha256', $key, 'TWIG', false);
        return $key;
    }

    /**
     * @inheritDoc
     */
    public function write(string $key, string $content): void
    {
        apcu_store(
            $key, [
            'data' => 'data://text/plain;base64,' . base64_encode($content),
            'timestamp' => time(),
            ]
        );
    }

    /**
     * @inheritDoc
     */
    public function load(string $key): void
    {
        $content = apcu_fetch($key);

        if ($content !== false) {
            @include $content['data'];
        }
    }

    /**
     * @inheritDoc
     */
    public function getTimestamp(string $key): int
    {
        $content = apcu_fetch($key);

        if ($content !== false) {
            return $content['timestamp'];
        }

        return 0;
    }
}

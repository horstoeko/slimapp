<?php

declare(strict_types=1);

namespace horstoeko\slimapp\system;

class SlimAppEnvironment
{
    public static function env($key, $adefault = null)
    {
        if ($key === 'HTTPS') {
            if (isset($_SERVER['HTTPS'])) {
                return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
            }
            return (strpos(self::env('SCRIPT_URI'), 'https://') === 0);
        }

        if ($key === 'SCRIPT_NAME') {
            if (self::env('CGI_MODE') && isset($_ENV['SCRIPT_URL'])) {
                $key = 'SCRIPT_URL';
            }
        }

        $val = null;
        if (isset($_SERVER[$key])) {
            $val = $_SERVER[$key];
        } elseif (isset($_ENV[$key])) {
            $val = $_ENV[$key];
        } elseif (getenv($key) !== false) {
            $val = getenv($key);
        }

        if ($key === 'REMOTE_ADDR' && $val === self::env('SERVER_ADDR')) {
            $addr = self::env('HTTP_PC_REMOTE_ADDR');
            if ($addr !== null) {
                $val = $addr;
            } else {
                $addr = self::env('HTTP_CLIENT_IP');
                if ($addr !== null) {
                    $val = $addr;
                }
            }
        }

        if ($key === 'HTTP_X_FORWARDED_FOR' && $val !== null) {
            $val = preg_replace('/(?:,.*)/', '', $val);
        }

        if ($val !== null) {
            return $val;
        }

        switch ($key) {
            case 'DOCUMENT_ROOT':
                $name = self::env('SCRIPT_NAME');
                $filename = self::env('SCRIPT_FILENAME');
                $offset = 0;
                if (!strpos($name, '.php')) {
                    $offset = 4;
                }
                return substr($filename, 0, -(strlen($name) + $offset));
            case 'PHP_SELF':
                return str_replace(self::env('DOCUMENT_ROOT'), '', self::env('SCRIPT_FILENAME'));
            case 'CGI_MODE':
                return (PHP_SAPI === 'cgi');
        }

        return $adefault;
    }
}

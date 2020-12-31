<?php

declare(strict_types=1);

namespace horstoeko\slimapp\loader;

abstract class SlimAppBaseLoader
{
    /**
     * Returns the files which are to load
     *
     * @return array
     */
    protected abstract function getFiles(): array;

    /**
     * On after load and merge content of config file proceed the
     * final content here
     *
     * @param  array $content
     * @return void
     */
    protected abstract function onAfterLoad(array $content): void;

    /**
     * Load the files
     *
     * @return void
     */
    public function load(): void
    {
        $content = [];

        foreach ($this->getFiles() as $file) {
            if (!file_exists($file)) {
                continue;
            }

            $singleContent = include $file;

            $content = self::array_merge_recursive_distinct($content, $singleContent);
        }

        $this->onAfterLoad($content);
    }

    /**
     * Merges two array recursiv. It replaces existing values in the primary array __array1__
     *
     * @param  array   $array1
     * @param  array   $array2
     * @param  boolean $ignorenull
     * @return array
     */
    protected static function array_merge_recursive_distinct(array $array1, array $array2, $ignorenull = false)
    {
        $merged = $array1;

        foreach ($array2 as $key => &$value) {
            if (is_array($value) && isset($merged[$key]) && is_array($merged[$key])) {
                $merged[$key] = static::array_merge_recursive_distinct($merged[$key], $value);
            } else {
                if (isset($value) || !$ignorenull) {
                    $merged[$key] = $value;
                }
            }
        }
        return $merged;
    }
}

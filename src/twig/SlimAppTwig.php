<?php

namespace horstoeko\slimapp\twig;

use \ArrayAccess;
use \ArrayIterator;
use \Psr\Http\Message\ResponseInterface;
use \Psr\Http\Message\ResponseInterface as Response;
use \Twig\Environment;
use \Twig\Error\LoaderError;
use \Twig\Error\RuntimeError;
use \Twig\Error\SyntaxError;
use \Twig\Extension\AbstractExtension;
use \Twig\Loader\FilesystemLoader;
use \Twig\Loader\LoaderInterface;
use \Twig\RuntimeLoader\RuntimeLoaderInterface as TwigRuntimeLoaderInterface;

/**
 * Enhanced version auf \Slim\Views\Twig
 */
class SlimAppTwig implements ArrayAccess
{
    /**
     * Twig loader
     *
     * @var \Twig\Loader\FilesystemLoader
     */
    protected $loader;

    /**
     * Twig environment
     *
     * @var Environment
     */
    protected $environment;

    /**
     * Default view variables
     *
     * @var array
     */
    protected $defaultVariables = [];

    /**
     * @param string|array $path     Path(s) to templates directory
     * @param array        $settings Twig environment settings
     */
    public function __construct($path, $settings = [])
    {
        $this->loader = $this->createLoader(is_string($path) ? [$path] : $path);
        $this->environment = new Environment($this->loader, $settings);
    }

    /**
     * Proxy method to add an extension to the Twig environment
     *
     * @param AbstractExtension $extension A single extension instance or an array of instances
     */
    public function addExtension(AbstractExtension $extension)
    {
        $this->environment->addExtension($extension);
    }

    /**
     * Fetch rendered template
     *
     * @param string $template Template pathname relative to templates directory
     * @param array  $data     Associative array of template variables
     *
     * @throws LoaderError  When the template cannot be found
     * @throws SyntaxError  When an error occurred during compilation
     * @throws RuntimeError When an error occurred during rendering
     *
     * @return string
     */
    public function fetch(string $template, array $data = [])
    {
        $data = array_merge($this->defaultVariables, $data);

        return $this->environment->render($template, $data);
    }

    /**
     * Fetch rendered string
     *
     * @param string $string String
     * @param array  $data   Associative array of template variables
     *
     * @return string
     */
    public function fetchFromString(string $string = '', array $data = [])
    {
        $data = array_merge($this->defaultVariables, $data);

        return $this->environment->createTemplate($string)->render($data);
    }

    /**
     * Output rendered template
     *
     * @param  ResponseInterface $response
     * @param  string            $template Template pathname relative to templates directory
     * @param  array             $data     Associative array of template variables
     * @return ResponseInterface
     */
    public function render(ResponseInterface $response, string $template, array $data = [])
    {
        $response->getBody()->write($this->fetch($template, $data));

        return $response;
    }

    /**
     * Create a loader with the given path
     *
     * @param  array $paths
     * @return FilesystemLoader
     */
    private function createLoader(array $paths)
    {
        $loader = new FilesystemLoader();

        foreach ($paths as $namespace => $path) {
            if (is_string($namespace)) {
                $loader->setPaths($path, $namespace);
            } else {
                $loader->addPath($path);
            }
        }

        return $loader;
    }

    /**
     * Return Twig loader
     *
     * @return LoaderInterface
     */
    public function getLoader(): LoaderInterface
    {
        return $this->loader;
    }

    /**
     * Return Twig environment
     *
     * @return Environment
     */
    public function getEnvironment(): Environment
    {
        return $this->environment;
    }

    /**
     * Does this collection have a given key?
     *
     * @param string $key The data key
     *
     * @return bool
     */
    public function offsetExists($key)
    {
        return array_key_exists($key, $this->defaultVariables);
    }

    /**
     * Get collection item for key
     *
     * @param string $key The data key
     *
     * @return mixed The key's value, or the default value
     */
    public function offsetGet($key)
    {
        return $this->defaultVariables[$key];
    }

    /**
     * Set collection item
     *
     * @param string $key   The data key
     * @param mixed  $value The data value
     */
    public function offsetSet($key, $value)
    {
        $this->defaultVariables[$key] = $value;
    }

    /**
     * Remove item from collection
     *
     * @param string $key The data key
     */
    public function offsetUnset($key)
    {
        unset($this->defaultVariables[$key]);
    }

    /**
     * Get number of items in collection
     *
     * @return int
     */
    public function count()
    {
        return count($this->defaultVariables);
    }

    /**
     * Get collection iterator
     *
     * @return ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->defaultVariables);
    }

    /**
     * Add template path(s)
     *
     * @param  string[] $path
     * @return void
     */
    public function addTemplatePath($path)
    {
        $paths = !is_array($path) ? [$path] : $path;

        foreach ($paths as $namespace => $path) {
            if (is_string($namespace)) {
                $this->loader->setPaths($path, $namespace);
            } else {
                $this->loader->addPath($path);
            }
        }
    }

    /**
     * Checks if a template exists
     *
     * @param  string $name
     * @return bool
     */
    public function exists($name)
    {
        return $this->loader->exists($name);
    }

    /**
     * Checks if any of the template in $names exists
     *
     * @param  array $names
     * @return bool
     */
    public function existsAny(array $names)
    {
        foreach ($names as $name) {
            if ($this->exists($name)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Render template, array for templates is
     * possible, the first existing will be used
     *
     * @return Response
     */
    public function renderExtended(Response $response, array $templates, $data = [])
    {
        foreach ($templates as $template) {
            if (!$this->exists($template)) {
                continue;
            }
            return $this->render($response, $template, $data);
        }
    }

    /**
     * Fetch a template content
     *
     * @param  array $templates
     * @param  array $data
     * @return string
     */
    public function fetchExtended(array $templates, $data = [])
    {
        foreach ($templates as $template) {
            if (!$this->exists($template)) {
                continue;
            }
            return $this->fetch($template, $data);
        }
        return "";
    }

    /**
     * Loader for runtimes
     *
     * @param  TwigRuntimeLoaderInterface $loader
     * @return void
     */
    public function addRuntimeLoader(TwigRuntimeLoaderInterface $loader)
    {
        $this->environment->addRuntimeLoader($loader);
    }
}

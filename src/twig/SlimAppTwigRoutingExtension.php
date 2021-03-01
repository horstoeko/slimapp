<?php

declare(strict_types=1);

namespace horstoeko\slimapp\twig;

use Twig\TwigFunction;
use Slim\Factory\ServerRequestCreatorFactory;
use horstoeko\slimapp\twig\SlimAppTwigExtension;
use Slim\Interfaces\RouteParserInterface;
use Slim\Interfaces\RouteCollectorInterface;

/**
 * SlimApp Routing Extensions for twig
 */
class SlimAppTwigRoutingExtension extends SlimAppTwigExtension
{
    /**
     * @var \Slim\Interfaces\RouteParserInterface
     */
    protected $routeParser;

    /**
     * @var \Slim\Interfaces\RouteCollectorInterface
     */
    protected $routeCollector;

    /**
     * Constructor
     *
     * @param RouteCollectorInterface $routeCollector
     * @param RouteParserInterface $routeParser
     */
    public function __construct(RouteCollectorInterface $routeCollector, RouteParserInterface $routeParser)
    {
        $this->routeCollector = $routeCollector;
        $this->routeParser = $routeParser;
    }

    /**
     * Get common name for this extension
     *
     * @return string
     */
    public function getName()
    {
        return 'slimapprouting';
    }

    /**
     * Get available functions in this extension
     *
     * @return array
     */
    public function getFunctions()
    {
        return [
            new TwigFunction(
                'urlFor',
                array($this, 'urlFor'),
                array('is_safe' => array('html'))
            ),
            new TwigFunction(
                'path_for',
                array($this, 'urlFor'),
                array('is_safe' => array('html'))
            ),
            new TwigFunction(
                'urlForRaw',
                array($this, 'urlForRaw'),
                array('is_safe' => array('html'))
            ),
            new TwigFunction(
                'path_for_raw',
                array($this, 'urlForRaw'),
                array('is_safe' => array('html'))
            ),
            new TwigFunction(
                'fullUrlFor',
                array($this, 'fullUrlFor'),
                array('is_safe' => array('html'))
            ),
            new TwigFunction(
                'is_current_url',
                array($this, 'currentUrlIs'),
                array('is_safe' => array('html'))
            ),
            new TwigFunction(
                'current_url_starts_with',
                array($this, 'currentUrlStartsWith'),
                array('is_safe' => array('html'))
            ),
            new TwigFunction(
                'urlExists',
                array($this, 'urlExists'),
                array('is_safe' => array('html'))
            ),
            new TwigFunction(
                'currentUrl',
                array($this, 'getCurrentUrl'),
                array('is_safe' => array('html'))
            ),
        ];
    }

    /**
     * Get route by name
     *
     * @param string $name
     * @param array $data
     * @param array $queryParams
     * @return string
     */
    public function urlFor(string $name, array $data = [], array $queryParams = []): string
    {
        return $this->routeParser->urlFor($name, $data, $queryParams);
    }

    /**
     * Get the route by name. The parameters are not parsed and
     * will be returned as placeholder
     *
     * @param string $name
     * @param bool $noException
     * @return string
     */
    public function urlForRaw(string $name, bool $noException = false): string
    {
        try {
            $basePath = $this->routeCollector->getBasePath();

            $route = $this->routeCollector->getNamedRoute($name);
            $pattern = $route->getPattern();

            if ($basePath) {
                $pattern = $basePath . $pattern;
            }
        } catch (\Exception $e) {
            $pattern = "";
            if ($noException != true) {
                throw $e;
            }
        }

        return $pattern;
    }

    /**
     * @param string $routeName   Route placeholders
     * @param array  $data        Route placeholders
     * @param array  $queryParams
     *
     * @return string
     */
    public function fullUrlFor(string $routeName, array $data = [], array $queryParams = [])
    {
        $serverRequestCreator = ServerRequestCreatorFactory::create();
        $request = $serverRequestCreator->createServerRequestFromGlobals();

        return $this->routeParser->fullUrlFor($request->getUri(), $routeName, $data, $queryParams);
    }

    /**
     * Check if current url is the given route
     *
     * @param string $routeName
     * @return bool
     */
    public function currentUrlIs(string $routeName)
    {
        try {
            $serverRequestCreator = ServerRequestCreatorFactory::create();
            $request = $serverRequestCreator->createServerRequestFromGlobals();
            $basePath = $this->routeCollector->getBasePath();

            $currentUrl = $basePath . $request->getUri()->getPath();
            $requestedUrl = $this->routeParser->urlFor($routeName);

            return $currentUrl == $requestedUrl;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Check if current url is the given route
     *
     * @param string $routeName
     * @return bool
     */
    public function currentUrlStartsWith(string $routeName)
    {
        try {
            $serverRequestCreator = ServerRequestCreatorFactory::create();
            $request = $serverRequestCreator->createServerRequestFromGlobals();
            $basePath = $this->routeCollector->getBasePath();

            $currentUrl = $basePath . $request->getUri()->getPath();
            $requestedUrl = $this->routeParser->urlFor($routeName);

            return stripos($currentUrl, $requestedUrl) === 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Check if the named route exists
     *
     * @param string $routeName
     * @return boolean
     */
    public function urlExists(string $routeName)
    {
        foreach ($this->routeCollector->getRoutes() as $route) {
            if ($routeName === $route->getName()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Returns the current url
     *
     * @param string $routeName
     * @return bool
     */
    public function getCurrentUrl()
    {
        try {
            $serverRequestCreator = ServerRequestCreatorFactory::create();
            $request = $serverRequestCreator->createServerRequestFromGlobals();
            $basePath = $this->routeCollector->getBasePath();

            $currentUrl = $basePath . $request->getUri()->getPath();

            return $currentUrl;
        } catch (\Exception $e) {
            return false;
        }
    }
}

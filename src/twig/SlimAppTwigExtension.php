<?php

namespace horstoeko\slimapp\twig;

use Psr\Container\ContainerInterface;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

class SlimAppTwigExtension extends AbstractExtension implements GlobalsInterface
{
    /**
     * Constructor
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Get common name for this extension
     *
     * @return string
     */
    public function getName()
    {
        return '';
    }

    /**
     * Get available functions in this extension
     *
     * @return array
     */
    public function getFunctions()
    {
        return [
        ];
    }

    /**
     * Get variables available in this extension
     *
     * @return array
     */
    public function getGlobals(): array
    {
        return [
        ];
    }

    /**
     * Get available filters in this extension
     *
     * @return array
     */
    public function getFilters()
    {
        return [
        ];
    }

    /**
     * Get available parsers
     *
     * @return array
     */
    public function getTokenParsers()
    {
        return [
        ];
    }
}

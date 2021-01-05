<?php

declare(strict_types=1);

namespace horstoeko\slimapp\loader;

use Slim\App;

class SlimAppRouteLoader extends SlimAppBaseLoader
{
    /**
     * @var \Slim\App
     */
    protected $app;

    /**
     * @inheritDoc
     */
    public function __construct(App $app)
    {
        $this->app = $app;
    }

    /**
     * @inheritDoc
     */
    protected function getFiles(): array
    {
        return [
            __DIR__ . "/../etc/routes.php",
            __DIR__ . "/../../../../../etc/routes.php",
        ];
    }

    /**
     * @inheritDoc
     */
    protected function onAfterLoad(array $content): void
    {
        foreach ($content as $routename => $route) {
            $method = $route['method'] ?? "";
            $pattern = $route['pattern'] ?? "";
            $callback = $route['callback'] ?? null;
            $middlewares = $route['middlewares'] ?? [];

            if (!is_string($pattern) || (!is_string($method) && !is_array($method)) || !is_array($middlewares)) {
                continue;
            }

            if ($pattern == "") {
                continue;
            }

            if (!is_array($method)) {
                $method = [$method];
            }

            $route = $this->app->map($method, $pattern, $callback)->setName($routename);

            foreach ($middlewares as $middleware) {
                $route->add($middleware);
            }
        }
    }
}

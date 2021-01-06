<?php

declare(strict_types=1);

namespace horstoeko\slimapp\loader;

use Slim\App;
use horstoeko\stringmanagement\PathUtils;

class SlimAppMiddlewareLoader extends SlimAppBaseLoader
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
        parent::__construct();

        $this->app = $app;
    }

    /**
     * @inheritDoc
     */
    protected function getFiles(): array
    {
        return [
            PathUtils::combinePathWithFile($this->directories->getvendorsettingsdirectory(), "middleware.php"),
            PathUtils::combinePathWithFile($this->directories->getcustomsettingsdirectory(), "middleware.php"),
        ];
    }

    /**
     * @inheritDoc
     */
    protected function onAfterLoad(array $content): void
    {
        foreach ($content as $middleware) {
            $this->app->add($middleware);
        }
    }
}

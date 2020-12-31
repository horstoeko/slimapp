<?php

declare(strict_types=1);

namespace horstoeko\slimapp\baseapp\action;

use Psr\Http\Message\ResponseInterface as Response;
use horstoeko\slimapp\action\SlimAppHtmlAction;

class HtmlIndexAction extends SlimAppHtmlAction
{
    /**
     * @inheritDoc
     */
    protected function resolveTemplate(): array
    {
        return ["@slimbaseapp/HtmlIndexAction.twig"];
    }

    /**
     * @inheritDoc
     */
    protected function action(): Response
    {
        return $this->respondWithData();
    }
}

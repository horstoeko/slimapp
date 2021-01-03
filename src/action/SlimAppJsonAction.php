<?php

declare(strict_types=1);

namespace horstoeko\slimapp\action;

use Psr\Http\Message\ResponseInterface as Response;

abstract class SlimAppJsonAction extends SlimAppBaseAction
{
    /**
     * @inheritDoc
     */
    protected function respondWithData($data = null, int $statusCode = 200): Response
    {
        $json = json_encode($data, JSON_PRETTY_PRINT);
        $this->response->getBody()->write($json);

        return $this->response->withHeader('Content-Type', 'application/json')->withStatus($statusCode);
    }
}

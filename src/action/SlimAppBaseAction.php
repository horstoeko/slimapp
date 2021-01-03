<?php

declare(strict_types=1);

namespace horstoeko\slimapp\action;

use Illuminate\Database\Capsule\Manager as Capsule;
use Psr\Log\LoggerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpBadRequestException;

abstract class SlimAppBaseAction
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var Capsule
     */
    protected $capsule;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Response
     */
    protected $response;

    /**
     * @var array
     */
    protected $args;

    /**
     * @param LoggerInterface $logger
     * @param Capsule         $capsule
     */
    public function __construct(LoggerInterface $logger, Capsule $capsule)
    {
        $this->logger = $logger;
        $this->capsule = $capsule;
    }

    /**
     * @param  Request  $request
     * @param  Response $response
     * @param  array    $args
     * @return Response
     * @throws HttpNotFoundException
     * @throws HttpBadRequestException
     */
    public function __invoke(Request $request, Response $response, $args): Response
    {
        $this->request = $request;
        $this->response = $response;
        $this->args = $args;

        return $this->action();
    }

    /**
     * @param  string $name
     * @return mixed
     * @throws HttpBadRequestException
     */
    protected function resolveArg(string $name)
    {
        if (!isset($this->args[$name])) {
            throw new HttpBadRequestException($this->request, "Could not resolve argument `{$name}`.");
        }

        return $this->args[$name];
    }

    /**
     * @return Response
     * @throws HttpBadRequestException
     */
    abstract protected function action(): Response;

    /**
     * @param  array|object|null $data
     * @return Response
     */
    abstract protected function respondWithData($data = null, int $statusCode = 200): Response;
}

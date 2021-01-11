<?php

declare(strict_types=1);

namespace horstoeko\slimapp\action;

use Illuminate\Database\Capsule\Manager as Capsule;
use Psr\Log\LoggerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpBadRequestException;
use horstoeko\slimapp\validation\SlimAppValidator;
use Symfony\Component\Translation\Translator;

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
     * @var SlimAppValidator
     */
    protected $validator;

    /**
     * @var Translator
     */
    protected $translator;

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
     * @var array
     */
    protected $queryParams;

    /**
     * @param LoggerInterface $logger
     * @param Capsule         $capsule
     */
    public function __construct(LoggerInterface $logger, Capsule $capsule, SlimAppValidator $validator, Translator $translator)
    {
        $this->logger = $logger;
        $this->capsule = $capsule;
        $this->validator = $validator;
        $this->translator = $translator;
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
        $this->queryParams = $request->getQueryParams();

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
     * Resolve a Query Parameter
     *
     * @param string $name
     * @return mixed
     * @throws HttpBadRequestException
     */
    protected function resolveQueryParam(string $name)
    {
        if (!isset($this->queryParams[$name])) {
            throw new HttpBadRequestException($this->request, "Could not resolve query parameter `{$name}`.");
        }

        return $this->queryParams[$name];
    }

    /**
     * Resolve a Query Parameter. If the parameter does not exist
     * the default value will be returned
     *
     * @param string $name
     * @param mixed $defaultValue
     * @return mixed
     */
    protected function resolveQueryParamWithDefault(string $name, $defaultValue)
    {
        if (!isset($this->queryParams[$name])) {
            return $defaultValue;
        }

        return $this->queryParams[$name];
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

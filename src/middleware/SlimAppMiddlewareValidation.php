<?php

declare(strict_types=1);

namespace horstoeko\slimapp\middleware;

use horstoeko\slimapp\exception\SlimAppValidationException;
use horstoeko\slimapp\middleware\SlimAppMiddlewareBase;
use Psr\Http\Message\ResponseFactoryInterface;
use PSr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class SlimAppMiddlewareValidation extends SlimAppMiddlewareBase
{
    /**
     * @var ResponseFactoryInterface
     */
    private $responseFactory;

    /**
     * Constructor
     *
     * @param ResponseFactoryInterface $responseFactory
     * @param array                    $options
     */
    public function __construct(
        ResponseFactoryInterface $responseFactory,
        array $options
    ) {
        $this->responseFactory = $responseFactory;

        if (is_array($options)) {
            foreach ($options as $optionName => $optionValue) {
                if (!property_exists($this, $optionName)) {
                    continue;
                }
                $this->$optionName = $optionValue;
            }
        }
    }

    /**
     * Handle middleware
     *
     * @param  Request        $request
     * @param  RequestHandler $handler
     * @return Response
     */
    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        return $this->process($request, $handler);
    }

    /**
     * Handle middleware
     *
     * @param  Request        $request
     * @param  RequestHandler $handler
     * @return Response
     */
    public function process(Request $request, RequestHandler $handler): Response
    {
        try {
            return $handler->handle($request);
        } catch (SlimAppValidationException $exception) {
            $response = $this->responseFactory->createResponse()
                ->withStatus(400)
                ->withHeader('Content-Type', 'application/json');

            $body = json_encode(["validationerror" => $exception->getValidatorErrors()], JSON_PRETTY_PRINT);
            $response->getBody()->write($body);

            return $response;
        }
    }
}

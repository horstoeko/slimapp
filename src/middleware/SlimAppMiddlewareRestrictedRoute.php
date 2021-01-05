<?php

declare(strict_types=1);

namespace horstoeko\slimapp\middleware;

use Psr\Http\Message\ResponseFactoryInterface;
use horstoeko\slimapp\security\SlimAppLoginManager;
use \PSr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;
use horstoeko\slimapp\traits\SlimAppDetermineContentTypeTrait;
use \Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class SlimAppMiddlewareRestrictedRoute extends SlimAppMiddlewareBase
{
    use SlimAppDetermineContentTypeTrait;

    /**
     * Loginmanager reference
     *
     * @var \horstoeko\slimapp\security\SlimAppLoginManager
     */
    protected $loginManager;

    /**
     * Response factory
     *
     * @var \Psr\Http\Message\ResponseFactoryInterface
     */
    protected $responseFactory;

    /**
     * Failure redirects
     *
     * @var array
     */
    protected $failureRedirects = [];

    /**
     * Default redirect route
     *
     * @var string|null
     */
    protected $defaultFailureRedirect = null;

    /**
     * Constructor
     */
    public function __construct(
        SlimAppLoginManager $loginManager,
        ResponseFactoryInterface $responseFactory,
        array $options
    ) {
        $this->loginManager = $loginManager;
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
     * Add a redirect if failure occurs
     *
     * @param string $prefix
     * @param string $redirectTo
     * @return void
     */
    public function addFailureRedirect($prefix, $redirectTo)
    {
        if (!isset($this->failureRedirects[$prefix])) {
            $this->failureRedirects[$prefix] = $redirectTo;
        }

        return $this;
    }

    /**
     * Handle middleware
     *
     * @param Request $request
     * @param RequestHandler $handler
     * @return Response
     */
    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        return $this->process($request, $handler);
    }

    /**
     * Handle middleware
     *
     * @param Request $request
     * @param RequestHandler $handler
     * @return Response
     */
    public function process(Request $request, RequestHandler $handler): Response
    {
        if (!$this->getIsSignedIn()) {
            $currentUrl = $request->getUri()->getPath();
            $redirectTo = "";

            if ($this->isHtmlRequest($request)) {
                foreach ($this->failureRedirects as $failureRedirectFrom => $failureRedirectTo) {
                    if (preg_match($failureRedirectFrom, $currentUrl) === 1) {
                        $redirectTo = $failureRedirectTo;
                        break;
                    }
                }

                if (isset($this->defaultFailureRedirect) &&
                    $this->defaultFailureRedirect != "" &&
                    (!isset($redirectTo) || $redirectTo == "")) {
                    $redirectTo = $this->defaultFailureRedirect;
                }

                $response = $this->responseFactory->createResponse();

                if ($redirectTo) {
                    return $response->withHeader('Location', $redirectTo)->withStatus(302);
                }
            }

            throw new \Slim\Exception\HttpUnauthorizedException($request);
        } else {
            $response = $handler->handle($request);
        }

        return $response;
    }

    /**
     * Must be overiden in child classes
     *
     * @return bool
     */
    protected function getIsSignedIn()
    {
        return true;
    }
}

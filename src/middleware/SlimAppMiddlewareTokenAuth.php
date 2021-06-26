<?php

declare(strict_types=1);

namespace horstoeko\slimapp\middleware;

use \horstoeko\slimapp\security\SlimAppLoginManager;
use \Psr\Http\Message\ResponseFactoryInterface;
use \PSr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class SlimAppMiddlewareTokenAuth extends SlimAppMiddlewareBase
{
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
     * Check for HTTPS request
     *
     * @var boolean
     */
    protected $secure = true;

    /**
     * Host which will not be checked against https
     *
     * @var array
     */
    protected $relaxed = ["localhost", "127.0.0.1", "192.168.1.0/24"];

    /**
     * If this property is set to true and a token header was not
     * transmitted no error will occur. You must ensure to authenticate
     * a user in another way (e.g. basic auth)
     *
     * @var boolean
     */
    protected $optional = true;

    /**
     * Constructor
     *
     * @param SlimAppLoginManager $loginManager
     * @param array $options
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
        if ($this->loginManager->isSignedIn() === false) {
            $host = $request->getUri()->getHost();
            $client = $request->getAttribute('ip_address');
            $scheme = $request->getUri()->getScheme();

            if ("https" !== $scheme && true === $this->secure) {
                $allowedHost = $this->isRelaxedHost([$host, $client], $this->relaxed);
                $allowedForward = false;

                if (in_array("headers", $this->relaxed)) {
                    if (
                        $request->getHeaderLine("X-Forwarded-Proto") === "https" &&
                        $request->getHeaderLine('X-Forwarded-Port') === "443"
                    ) {
                        $allowedForward = true;
                    }
                }

                if (!($allowedHost || $allowedForward)) {
                    throw new \RuntimeException(
                        sprintf("Insecure use of middleware over %s denied by configuration.", strtoupper($scheme))
                    );
                }
            }

            $token = "";
            $authenticatedByToken = false;

            if (preg_match("/Bearer\s+(.*)$/i", $request->getHeaderLine("Authorization"), $matches)) {
                $token = $matches[1];
            } else {
                $queryParams = $request->getQueryParams();
                if (isset($queryParams["private_token"]) && $queryParams["private_token"] != "") {
                    $token = $request->getQueryParams()["private_token"];
                }
                else if (isset($queryParams["token"]) && $queryParams["token"] != "") {
                    $token = $request->getQueryParams()["token"];
                }
            }

            if (!$token && $this->optional === true) {
                // Do nothing if no token was presented and the
                // optional mode is active
            } else {
                if ($this->loginManager->loginUserByToken($token) === false) {
                    if ($this->isJsonRequest($request)) {
                        return $this->responseFactory->createResponse()->withStatus(401);
                    } else {
                        return $this->responseFactory->createResponse()->withStatus(401);
                    }
                } else {
                    $authenticatedByToken = true;
                }
            }
        }

        $response = $handler->handle($request);

        if ($authenticatedByToken === true) {
            $this->loginManager->logoutUser();
        }

        return $response;
    }

    /**
     * Check if the calling host is excepted from https
     *
     * @param string|array $host
     * @return boolean
     */
    protected function isRelaxedHost($host)
    {
        if (is_array($host)) {
            foreach ($host as $hostitem) {
                if ($this->isRelaxedHost($hostitem)) {
                    return true;
                }
            }
        } else {
            foreach ($this->relaxed as $relaxeditem) {
                if (
                    preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}(\/([0-9]|[1-2][0-9]|3[0-2]))?$/im', $relaxeditem) &&
                    ($this->cidrMatch($host, $relaxeditem))
                ) {
                    return true;
                } elseif ($host == $relaxeditem) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Check ip is in range
     *
     * @param string $ip
     * @param string $cidr
     * @return boolean
     */
    protected function cidrMatch(string $ip, string $cidr): bool
    {
        list($subnet, $mask) = explode('/', $cidr);

        if ((ip2long($ip) & ~((1 << (32 - $mask)) - 1)) == ip2long($subnet)) {
            return true;
        }

        return false;
    }

    /**
     * Returns true if request is a json request
     *
     * @return boolean
     */
    protected function isJsonRequest(Request $request): bool
    {
        $cType = $request->getHeaderLine('Content-Type');
        return stripos('application/json', $cType) !== false;
    }
}

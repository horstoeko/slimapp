<?php

declare(strict_types=1);

namespace horstoeko\slimapp\middleware;

class SlimAppMiddlewareRestrictedRouteLight extends SlimAppMiddlewareRestrictedRoute
{
    protected function getIsSignedIn()
    {
        return $this->loginManager->IsSignedIn();
    }
}

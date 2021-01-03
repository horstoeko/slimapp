<?php

declare(strict_types=1);

namespace horstoeko\slimapp\middleware;

class SlimAppMiddlewareRestrictedRouteAdmin extends SlimAppMiddlewareRestrictedRoute
{
    protected function GetIsSignedIn()
    {
        return $this->loginManager->IsAdminSignedIn();
    }
}

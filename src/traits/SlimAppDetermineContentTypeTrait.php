<?php

namespace horstoeko\slimapp\traits;

use \Psr\Http\Message\ServerRequestInterface as Request;

trait SlimAppDetermineContentTypeTrait
{
    protected function determineContentType(Request $request): ?string
    {
        $knownContentTypes = [
            'application/json',
            'application/xml',
            'text/xml',
            'text/html',
        ];

        $acceptHeader = $request->getHeaderLine('Accept');
        $selectedContentTypes = array_intersect(explode(',', $acceptHeader), $knownContentTypes);
        $count = count($selectedContentTypes);

        if ($count) {
            $current = current($selectedContentTypes);

            if ($current === 'text/plain' && $count > 1) {
                return next($selectedContentTypes);
            }

            return $current;
        }

        if (preg_match('/\+(json|xml)/', $acceptHeader, $matches)) {
            $mediaType = 'application/' . $matches[1];
            return $mediaType;
        }

        return null;
    }

    protected function isHtmlRequest(Request $request)
    {
        return stripos($this->determineContentType($request), "text/html") === 0;
    }

    protected function isJsonRequest(Request $request)
    {
        return stripos($this->determineContentType($request), "application/json") === 0;
    }

    protected function isXmlRequest(Request $request)
    {
        return
            (stripos($this->determineContentType($request), "text/xml") === 0 ) ||
            (stripos($this->determineContentType($request), "application/xml") === 0);
    }
}

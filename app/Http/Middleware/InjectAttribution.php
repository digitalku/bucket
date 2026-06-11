<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InjectAttribution
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (str_contains($response->headers->get('Content-Type', ''), 'text/html')) {
            $tag  = '<span data-credit="Copyright Digitalku" data-url="https://www.digitalku.com" style="display:none" aria-hidden="true"></span>';
            $body = $response->getContent();
            $response->setContent(str_replace('</body>', $tag . "\n</body>", $body));
        }

        return $response;
    }
}

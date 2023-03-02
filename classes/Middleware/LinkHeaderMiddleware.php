<?php

namespace Alltube\Middleware;

use Alltube\Factory\ViewFactory;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Class LinkHeaderMiddleware
 * @package Alltube
 */
class LinkHeaderMiddleware
{
    /**
     * @param Request $request
     * @param Response $response
     * @param callable $next
     * @return mixed
     */
    public function __invoke(Request $request, Response $response, callable $next)
    {
        $uri = ViewFactory::prepareUri($request);

        $response = $response->withHeader(
            'Link',
            implode(
                '; ',
                [
                    '<' . $uri->getBasePath() . '/css/style.css>',
                    'rel=preload', 'as=style'
                ]
            )
        );

        return $next($request, $response);
    }
}

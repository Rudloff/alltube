<?php

namespace Alltube\Middleware;

use Psr\Container\ContainerInterface;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Router;

/**
 * Class LinkHeaderMiddleware
 * @package Alltube
 */
class LinkHeaderMiddleware
{
    /**
     * @var Router
     */
    private $router;

    /**
     * LinkHeaderMiddleware constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->router = $container->get('router');
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param callable $next
     * @return mixed
     */
    public function __invoke(Request $request, Response $response, callable $next)
    {
        $response = $response->withHeader(
            'Link',
            '<' . $this->router->getBasePath() . '/css/style.css>; rel=preload; as=style'
        );


        return $next($request, $response);
    }
}

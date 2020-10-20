<?php

namespace Alltube\Middleware;

use Psr\Container\ContainerInterface;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Router;

/**
 * Class RouterPathMiddleware
 * @package Alltube
 */
class RouterPathMiddleware
{
    /**
     * @var Router
     */
    private $router;

    /**
     * RouterPathMiddleware constructor.
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
        if ($path = current($request->getHeader('X-Forwarded-Path'))) {
            $this->router->setBasePath($path);
        }

        return $next($request, $response);
    }
}

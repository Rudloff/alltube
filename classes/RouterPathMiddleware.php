<?php

namespace Alltube;

use Psr\Container\ContainerInterface;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Interfaces\RouterInterface;

/**
 * Class RouterPathMiddleware
 * @package Alltube
 */
class RouterPathMiddleware
{
    /**
     * @var RouterInterface
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
        $this->router->setBasePath(current($request->getHeader('X-Forwarded-Path')));

        return $next($request, $response);
    }
}

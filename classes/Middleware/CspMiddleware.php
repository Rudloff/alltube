<?php

namespace Alltube\Middleware;

use Alltube\Config;
use ParagonIE\CSPBuilder\CSPBuilder;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\MessageInterface;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Class CspMiddleware
 * @package Alltube
 */
class CspMiddleware
{
    /**
     * @var Config
     */
    private $config;

    /**
     * CspMiddleware constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->config = $container->get('config');
    }

    /**
     * @param Response $response
     * @return MessageInterface
     */
    public function applyHeader(Response $response): MessageInterface
    {
        $csp = new CSPBuilder();
        $csp->disableOldBrowserSupport()
            ->addDirective('default-src', [])
            ->addDirective('font-src', ['self' => true])
            ->addDirective('style-src', ['self' => true])
            ->addDirective('manifest-src', ['self' => true])
            ->addDirective('base-uri', [])
            ->addDirective('frame-ancestors', [])
            ->addSource('form-action', '*')
            ->addSource('img-src', '*');

        if ($this->config->debug) {
            // So maximebf/debugbar, symfony/debug and symfony/error-handler can work.
            $csp->setDirective('script-src', ['self' => true, 'unsafe-inline' => true])
                ->setDirective('style-src', ['self' => true, 'unsafe-inline' => true])
                ->addSource('img-src', 'data:');
        }

        return $csp->injectCSPHeader($response);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param callable $next
     * @return mixed
     */
    public function __invoke(Request $request, Response $response, callable $next)
    {
        $response = $this->applyHeader($response);

        return $next($request, $response);
    }
}

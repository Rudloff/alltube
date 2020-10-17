<?php

/**
 * ViewFactory class.
 */

namespace Alltube;

use Psr\Container\ContainerInterface;
use Slim\Http\Request;
use Slim\Views\Smarty;
use Slim\Views\SmartyPlugins;
use SmartyException;

/**
 * Create Smarty view object.
 */
class ViewFactory
{
    /**
     * Create Smarty view object.
     *
     * @param ContainerInterface $container Slim dependency container
     * @param Request|null $request PSR-7 request
     *
     * @return Smarty
     * @throws SmartyException
     */
    public static function create(ContainerInterface $container, Request $request = null)
    {
        if (!isset($request)) {
            $request = $container->get('request');
        }

        $view = new Smarty(__DIR__ . '/../templates/');

        $uri = $request->getUri();
        if (in_array('https', $request->getHeader('X-Forwarded-Proto'))) {
            $uri = $uri->withScheme('https')->withPort(443);
        }

        $port = ViewFactory::extractHeader($request, 'X-Forwarded-Port');
        if (!is_null($port)) {
            $uri = $uri->withPort(intVal($port));
        }

        $path = ViewFactory::extractHeader($request, 'X-Forwarded-Path');
        if (!is_null($path)) {
            $uri = $uri->withBasePath($path);
        }

        $request = $request->withUri($uri);


        /** @var LocaleManager $localeManager */
        $localeManager = $container->get('locale');

        $smartyPlugins = new SmartyPlugins($container->get('router'), $request->getUri()->withUserInfo(null));
        $view->registerPlugin('function', 'path_for', [$smartyPlugins, 'pathFor']);
        $view->registerPlugin('function', 'base_url', [$smartyPlugins, 'baseUrl']);
        $view->registerPlugin('block', 't', [$localeManager, 'smartyTranslate']);

        return $view;
    }

    /**
     * Extract the header name from the given request.
     * Multiple headers with the same name are not supported; the first one would be returned in this case.
     *
     * @param Request|null $request PSR-7 request
     * @param string $headerName name of the header
     *
     * @return string|null
     */
    public static function extractHeader(Request $request = null, string $headerName)
    {
        if (is_null($request)) {
            return null;
        }

        $header = $request->getHeader($headerName);
        $count = sizeof($header);
        if ($count < 1) {
            return null;
        }
        return $header[0];
    }

    /**
     * Get the basepath as specified via 'X-Forwarded-Path' from the request if present.
     *
     * @param Request|null $request PSR-7 request
     *
     * @return string|null
     */
    public static function getBasePath(Request $request = null)
    {
        return ViewFactory::extractHeader($request, 'X-Forwarded-Path');
    }
}

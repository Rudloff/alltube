<?php

/**
 * ViewFactory class.
 */

namespace Alltube\Factory;

use Alltube\LocaleManager;
use Junker\DebugBar\Bridge\SmartyCollector;
use Psr\Container\ContainerInterface;
use Slim\Http\Request;
use Slim\Http\Uri;
use Slim\Views\Smarty;
use Slim\Views\SmartyPlugins;
use SmartyException;

/**
 * Create Smarty view object.
 */
class ViewFactory
{
    /**
     * @param Uri $uri
     * @return Uri
     */
    private static function cleanBasePath(Uri $uri): Uri
    {
        $basePath = $uri->getBasePath();
        if (str_ends_with($basePath, 'index.php')) {
            $basePath = dirname($basePath);
            if ($basePath == '/') {
                /*
                 * Calling withBasePath('/') does nothing,
                 * we have to use an empty string instead.
                 */
                $basePath = '';
            }

            /*
             * When the base path ends with index.php,
             * routing works correctly, but it breaks the URL of static assets using {base_url}.
             * So we alter the base path but only in the URI used by SmartyPlugins.
             */
            $uri = $uri->withBasePath($basePath);
        }

        return $uri;
    }

    /**
     * Create a URI suitable for templates.
     *
     * @param Request $request
     * @return Uri
     */
    public static function prepareUri(Request $request): Uri
    {
        /** @var Uri $uri */
        $uri = $request->getUri();
        if (in_array('https', $request->getHeader('X-Forwarded-Proto'))) {
            $uri = $uri->withScheme('https')->withPort(443);
        }

        // set values from X-Forwarded-* headers
        if ($host = current($request->getHeader('X-Forwarded-Host'))) {
            $uri = $uri->withHost($host);
        }

        if ($port = current($request->getHeader('X-Forwarded-Port'))) {
            $uri = $uri->withPort(intVal($port));
        }

        if ($path = current($request->getHeader('X-Forwarded-Path'))) {
            $uri = $uri->withBasePath($path);
        }

        return self::cleanBasePath($uri);
    }

    /**
     * Create Smarty view object.
     *
     * @param ContainerInterface $container Slim dependency container
     * @param Request|null $request PSR-7 request
     *
     * @return Smarty
     * @throws SmartyException
     */
    public static function create(ContainerInterface $container, Request $request = null): Smarty
    {
        if (!isset($request)) {
            $request = $container->get('request');
        }

        $view = new Smarty($container->get('root_path') . '/templates/');

        $uri = self::prepareUri($request);

        /** @var LocaleManager $localeManager */
        $localeManager = $container->get('locale');

        $smartyPlugins = new SmartyPlugins($container->get('router'), $uri->withUserInfo(''));
        $view->registerPlugin('function', 'path_for', [$smartyPlugins, 'pathFor']);
        $view->registerPlugin('function', 'base_url', [$smartyPlugins, 'baseUrl']);
        $view->registerPlugin('block', 't', [$localeManager, 'smartyTranslate']);

        $view->offsetSet('locale', $container->get('locale'));
        $view->offsetSet('config', $container->get('config'));
        $view->offsetSet('domain', $uri->withBasePath('')->getBaseUrl());

        if ($container->has('debugbar')) {
            $debugBar = $container->get('debugbar');
            $collector = new SmartyCollector($view->getSmarty());
            $collector->useHtmlVarDumper();
            $debugBar->addCollector($collector);

            $view->offsetSet(
                'debug_render',
                $debugBar->getJavascriptRenderer(
                    $uri->getBaseUrl() . '/vendor/maximebf/debugbar/src/DebugBar/Resources/'
                )
            );
        }

        return $view;
    }
}

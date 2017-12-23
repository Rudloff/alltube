<?php
/**
 * ViewFactory class.
 */

namespace Alltube;

use Psr\Container\ContainerInterface;
use Slim\Http\Request;
use Slim\Views\Smarty;
use Slim\Views\SmartyPlugins;

/**
 * Create Smarty view object.
 */
class ViewFactory
{
    /**
     * Create Smarty view object.
     *
     * @param ContainerInterface $container Slim dependency container
     * @param Request            $request   PSR-7 request
     *
     * @return Smarty
     */
    public static function create(ContainerInterface $container, Request $request = null)
    {
        if (!isset($request)) {
            $request = $container['request'];
        }

        $view = new Smarty(__DIR__.'/../templates/');
        if (in_array('https', $request->getHeader('X-Forwarded-Proto'))) {
            $request = $request->withUri($request->getUri()->withScheme('https')->withPort(443));
        }

        $smartyPlugins = new SmartyPlugins($container['router'], $request->getUri()->withUserInfo(null));
        $view->registerPlugin('function', 'path_for', [$smartyPlugins, 'pathFor']);
        $view->registerPlugin('function', 'base_url', [$smartyPlugins, 'baseUrl']);

        return $view;
    }
}

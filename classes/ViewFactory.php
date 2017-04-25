<?php
/**
 * ViewFactory class.
 */
namespace Alltube;

use Slim\Container;
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
     * @param  Container $container Slim dependency container
     * @param  Request   $request   PSR-7 request
     *
     * @return Smarty
     */
    public static function create(Container $container, Request $request = null)
    {
        if (!isset($request)) {
            $request = $container['request'];
        }

        $view = new Smarty(__DIR__.'/../templates/');

        $smartyPlugins = new SmartyPlugins($container['router'], $request->getUri());
        $view->registerPlugin('function', 'path_for', [$smartyPlugins, 'pathFor']);
        $view->registerPlugin('function', 'base_url', [$smartyPlugins, 'baseUrl']);

        $view->registerPlugin('modifier', 'noscheme', 'Smarty_Modifier_noscheme');

        return $view;
    }
}

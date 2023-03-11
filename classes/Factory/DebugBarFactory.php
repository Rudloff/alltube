<?php

namespace Alltube\Factory;

use DebugBar\DataCollector\ConfigCollector;
use DebugBar\DataCollector\MemoryCollector;
use DebugBar\DataCollector\MessagesCollector;
use DebugBar\DataCollector\PhpInfoCollector;
use DebugBar\DataCollector\RequestDataCollector;
use DebugBar\DebugBar;
use DebugBar\DebugBarException;
use Kitchenu\Debugbar\DataCollector\SlimRouteCollector;
use Slim\Container;

/**
 * Class DebugBarFactory
 * @package Alltube\Factory
 */
class DebugBarFactory
{
    /**
     * @param Container $container
     * @return DebugBar
     * @throws DebugBarException
     */
    public static function create(Container $container): DebugBar
    {
        $debugBar = new DebugBar();

        $requestCollector = new RequestDataCollector();
        $configCollector = new ConfigCollector(get_object_vars($container->get('config')));

        $debugBar->addCollector(new PhpInfoCollector())
            ->addCollector(new MessagesCollector())
            ->addCollector($requestCollector)
            ->addCollector(new MemoryCollector())
            ->addCollector($configCollector)
            ->addCollector(new SlimRouteCollector($container->get('router'), $container->get('request')));

        $container->get('logger')->add('debugbar', $debugBar->getCollector('messages'));

        $requestCollector->useHtmlVarDumper();
        $configCollector->useHtmlVarDumper();

        return $debugBar;
    }
}

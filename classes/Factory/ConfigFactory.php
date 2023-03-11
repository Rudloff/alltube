<?php

namespace Alltube\Factory;

use Alltube\Config;
use Alltube\Exception\ConfigException;
use Alltube\UglyRouter;
use Slim\Container;
use Symfony\Component\ErrorHandler\Debug;

/**
 * Class ConfigFactory
 * @package Alltube
 */
class ConfigFactory
{
    /**
     * @param Container $container
     * @return Config
     * @throws ConfigException
     */
    public static function create(Container $container): Config
    {
        $configPath = $container->get('root_path') . '/config/config.yml';
        if (is_file($configPath)) {
            $config = Config::fromFile($configPath);
        } else {
            $config = new Config();
        }
        if ($config->uglyUrls) {
            $container['router'] = new UglyRouter();
        }
        if ($config->debug) {
            /*
             We want to enable this as soon as possible,
             in order to catch errors that are thrown
             before the Slim error handler is ready.
             */
            Debug::enable();
        }

        return $config;
    }
}

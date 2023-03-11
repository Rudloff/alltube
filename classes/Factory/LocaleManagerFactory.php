<?php

namespace Alltube\Factory;

use Alltube\Exception\DependencyException;
use Alltube\LocaleManager;
use Locale;
use Slim\Container;

/**
 * Class LocaleManagerFactory
 * @package Alltube
 */
class LocaleManagerFactory
{
    /**
     * @param Container $container
     * @return LocaleManager
     * @throws DependencyException
     */
    public static function create(Container $container): LocaleManager
    {
        if (!class_exists(Locale::class)) {
            throw new DependencyException('You need to install the intl extension for PHP.');
        }

        return new LocaleManager($container->get('session'));
    }
}

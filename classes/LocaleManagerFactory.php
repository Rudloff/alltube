<?php

namespace Alltube;

use Alltube\Exception\DependencyException;

/**
 * Class LocaleManagerFactory
 * @package Alltube
 */
class LocaleManagerFactory
{

    /**
     * @return LocaleManager|null
     * @throws DependencyException
     */
    public static function create()
    {
        if (!class_exists('Locale')) {
            throw new DependencyException('You need to install the intl extension for PHP.');
        }

        return LocaleManager::getInstance();
    }
}

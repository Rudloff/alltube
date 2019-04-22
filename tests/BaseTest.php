<?php
/**
 * PlaylistArchiveStreamTest class.
 */

namespace Alltube\Test;

use Alltube\Config;
use PHPUnit\Framework\TestCase;

/**
 * Abstract class used by every test.
 */
abstract class BaseTest extends TestCase
{
    /**
     * Get the config file used in tests.
     *
     * @return string Path to file
     */
    protected function getConfigFile()
    {
        if (PHP_OS == 'WINNT') {
            $configFile = 'config_test_windows.yml';
        } else {
            $configFile = 'config_test.yml';
        }

        return __DIR__.'/../config/'.$configFile;
    }

    /**
     * Prepare tests.
     */
    protected function setUp()
    {
        Config::setFile($this->getConfigFile());
    }

    /**
     * Destroy properties after test.
     */
    protected function tearDown()
    {
        Config::destroyInstance();
    }
}

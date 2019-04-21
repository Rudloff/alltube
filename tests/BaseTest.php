<?php
/**
 * PlaylistArchiveStreamTest class.
 */

namespace Alltube\Test;

use Alltube\Config;
use Alltube\Video;
use Alltube\PlaylistArchiveStream;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use stdClass;

/**
 * Unit tests for the ViewFactory class.
 */
abstract class BaseTest extends TestCase
{

    /**
     * Prepare tests.
     */
    protected function setUp()
    {
        if (PHP_OS == 'WINNT') {
            $configFile = 'config_test_windows.yml';
        } else {
            $configFile = 'config_test.yml';
        }

        Config::setFile(__DIR__.'/../config/'.$configFile);
    }

    /**
     * Destroy properties after test.
     */
    protected function tearDown()
    {
        Config::destroyInstance();
    }
}

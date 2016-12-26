<?php
/**
 * ConfigTest class.
 */

namespace Alltube\Test;

use Alltube\Config;

/**
 * Unit tests for the Config class.
 */
class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Config class instance.
     *
     * @var Config
     */
    private $config;

    /**
     * Prepare tests.
     */
    protected function setUp()
    {
        $this->config = Config::getInstance('config_test.yml');
    }

    /**
     * Destroy variables created by setUp().
     * @return void
     */
    protected function tearDown()
    {
        Config::destroyInstance();
    }

    /**
     * Test the getInstance function.
     *
     * @return void
     */
    public function testGetInstance()
    {
        $this->assertEquals($this->config->convert, false);
        $this->assertInternalType('array', $this->config->curl_params);
        $this->assertInternalType('array', $this->config->params);
        $this->assertInternalType('string', $this->config->youtubedl);
        $this->assertInternalType('string', $this->config->python);
        $this->assertInternalType('string', $this->config->avconv);
        $this->assertInternalType('string', $this->config->rtmpdump);
    }

    /**
     * Test the getInstance function with a missing config file.
     *
     * @return void
     * @expectedException Exception
     */
    public function testGetInstanceWithMissingFile()
    {
        Config::getInstance('foo');
    }

    /**
     * Test the getInstance function with the CONVERT and PYTHON environment variables.
     *
     * @return void
     */
    public function testGetInstanceWithEnv()
    {
        Config::destroyInstance();
        putenv('CONVERT=1');
        putenv('PYTHON=foo');
        $config = Config::getInstance('config_test.yml');
        $this->assertEquals($config->convert, true);
        $this->assertEquals($config->python, 'foo');
        putenv('CONVERT');
        putenv('PYTHON');
    }
}

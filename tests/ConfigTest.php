<?php
/**
 * ConfigTest class.
 */

namespace Alltube\Test;

use Alltube\Config;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for the Config class.
 */
class ConfigTest extends TestCase
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
        $this->config = Config::getInstance('config/config_test.yml');
    }

    /**
     * Destroy variables created by setUp().
     *
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
        $this->assertConfig($this->config);
    }

    /**
     * Assert that a Config object is correctly instantiated.
     *
     * @param Config $config Config class instance.
     *
     * @return void
     */
    private function assertConfig(Config $config)
    {
        $this->assertInternalType('array', $config->params);
        $this->assertInternalType('string', $config->youtubedl);
        $this->assertInternalType('string', $config->python);
        $this->assertInternalType('string', $config->avconv);
        $this->assertInternalType('bool', $config->convert);
        $this->assertInternalType('bool', $config->uglyUrls);
        $this->assertInternalType('bool', $config->stream);
        $this->assertInternalType('bool', $config->remux);
        $this->assertInternalType('int', $config->audioBitrate);
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
     * Test the getInstance function with an empty filename.
     *
     * @return void
     */
    public function testGetInstanceWithEmptyFile()
    {
        $config = Config::getInstance('');
        $this->assertConfig($config);
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
        $config = Config::getInstance('config/config_test.yml');
        $this->assertEquals($config->convert, true);
        $this->assertEquals($config->python, 'foo');
        putenv('CONVERT');
        putenv('PYTHON');
    }
}

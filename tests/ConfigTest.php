<?php

/**
 * ConfigTest class.
 */

namespace Alltube\Test;

use Alltube\Config;
use Exception;

/**
 * Unit tests for the Config class.
 */
class ConfigTest extends BaseTest
{
    /**
     * Config class instance.
     *
     * @var Config
     */
    private $config;

    /**
     * Prepare tests.
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->config = Config::getInstance();
    }

    /**
     * Test the getInstance function.
     *
     * @return void
     */
    public function testGetInstance()
    {
        $config = Config::getInstance();
        $this->assertEquals(false, $config->convert);
        $this->assertConfig($config);
    }

    /**
     * Test the getInstance function.
     *
     * @return void
     */
    public function testGetInstanceFromScratch()
    {
        Config::destroyInstance();

        $config = Config::getInstance();
        $this->assertEquals(false, $config->convert);
        $this->assertConfig($config);
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
        $this->assertIsArray($config->params);
        $this->assertIsString($config->youtubedl);
        $this->assertIsString($config->python);
        $this->assertIsString($config->avconv);
        $this->assertIsBool($config->convert);
        $this->assertIsBool($config->uglyUrls);
        $this->assertIsBool($config->stream);
        $this->assertIsBool($config->remux);
        $this->assertIsInt($config->audioBitrate);
    }

    /**
     * Test the setFile function.
     *
     * @return void
     * @throws Exception
     */
    public function testSetFile()
    {
        Config::setFile($this->getConfigFile());
        $this->assertConfig($this->config);
    }

    /**
     * Test the setFile function with a missing config file.
     *
     * @return void
     */
    public function testSetFileWithMissingFile()
    {
        $this->expectException(Exception::class);
        Config::setFile('foo');
    }

    /**
     * Test the setOptions function.
     *
     * @return void
     * @throws Exception
     */
    public function testSetOptions()
    {
        Config::setOptions(['appName' => 'foo']);
        $config = Config::getInstance();
        $this->assertEquals('foo', $config->appName);
    }

    /**
     * Test the setOptions function.
     *
     * @return void
     * @throws Exception
     */
    public function testSetOptionsWithoutUpdate()
    {
        Config::setOptions(['appName' => 'foo'], false);
        $config = Config::getInstance();
        $this->assertEquals('foo', $config->appName);
    }

    /**
     * Test the setOptions function.
     *
     * @return void
     */
    public function testSetOptionsWithBadYoutubedl()
    {
        $this->expectException(Exception::class);
        Config::setOptions(['youtubedl' => 'foo']);
    }

    /**
     * Test the setOptions function.
     *
     * @return void
     */
    public function testSetOptionsWithBadPython()
    {
        $this->expectException(Exception::class);
        Config::setOptions(['python' => 'foo']);
    }

    /**
     * Test the getInstance function with the CONVERT and PYTHON environment variables.
     *
     * @return void
     * @throws Exception
     */
    public function testGetInstanceWithEnv()
    {
        Config::destroyInstance();
        putenv('CONVERT=1');
        Config::setFile($this->getConfigFile());
        $config = Config::getInstance();
        $this->assertEquals(true, $config->convert);
        putenv('CONVERT');
    }
}

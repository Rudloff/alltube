<?php

/**
 * ConfigTest class.
 */

namespace Alltube\Test;

use Alltube\Config;
use Alltube\Exception\ConfigException;

/**
 * Unit tests for the Config class.
 */
class ConfigTest extends BaseTest
{

    /**
     * Test the getInstance function.
     *
     * @return void
     */
    public function testGetInstance()
    {
        $config = new Config();
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
        $this->assertIsString($config->ffmpeg);
        $this->assertIsString($config->basePath);
        $this->assertIsBool($config->convert);
        $this->assertIsBool($config->uglyUrls);
        $this->assertIsBool($config->stream);
        $this->assertIsBool($config->remux);
        $this->assertIsBool($config->defaultAudio);
        $this->assertIsBool($config->convertSeek);
        $this->assertIsInt($config->audioBitrate);
        $this->assertIsInt($config->forwardPort);
    }

    /**
     * Test the setFile function.
     *
     * @return void
     * @throws ConfigException
     */
    public function testSetFile()
    {
        $config = Config::fromFile($this->getConfigFile());
        $this->assertConfig($config);
    }

    /**
     * Test the setFile function with a missing config file.
     *
     * @return void
     */
    public function testSetFileWithMissingFile()
    {
        $this->expectException(ConfigException::class);
        Config::fromFile('foo');
    }

    /**
     * Test the setOptions function.
     *
     * @return void
     * @throws ConfigException
     */
    public function testSetOptions()
    {
        $config = new Config();
        $config->setOptions(['appName' => 'foo']);
        $this->assertEquals('foo', $config->appName);
    }

    /**
     * Test the setOptions function.
     *
     * @return void
     */
    public function testSetOptionsWithBadYoutubedl()
    {
        $this->expectException(ConfigException::class);
        $config = new Config();
        $config->setOptions(['youtubedl' => 'foo']);
    }

    /**
     * Test the setOptions function.
     *
     * @return void
     */
    public function testSetOptionsWithBadPython()
    {
        $this->expectException(ConfigException::class);
        $config = new Config();
        $config->setOptions(['python' => 'foo']);
    }

    /**
     * Test the getInstance function with the CONVERT and PYTHON environment variables.
     *
     * @return void
     * @throws ConfigException
     */
    public function testGetInstanceWithEnv()
    {
        putenv('CONVERT=1');
        $config = Config::fromFile($this->getConfigFile());
        $this->assertEquals(true, $config->convert);
        putenv('CONVERT');
    }

    /**
     * Test the getBasePath function.
     *
     * @return void
     */
    public function testGetBasePath()
    {
        Config::setOptions(['basePath' => '/foo']);
        $config = Config::getInstance();
        $this->assertEquals('/foo', $config->getBasePath(null, null));
    }
}

<?php
/**
 * ConfigTest class.
 */

namespace Alltube\Test;

use Alltube\Config;

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
     */
    protected function setUp()
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
        $this->assertEquals($config->convert, false);
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
        $this->assertEquals($config->convert, false);
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
     * Test the setFile function.
     *
     * @return void
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
     * @expectedException Exception
     */
    public function testSetFileWithMissingFile()
    {
        Config::setFile('foo');
    }

    /**
     * Test the setOptions function.
     *
     * @return void
     */
    public function testSetOptions()
    {
        Config::setOptions(['appName' => 'foo']);
        $config = Config::getInstance();
        $this->assertEquals($config->appName, 'foo');
    }

    /**
     * Test the setOptions function.
     *
     * @return void
     */
    public function testSetOptionsWithoutUpdate()
    {
        if (getenv('APPVEYOR')) {
            $this->markTestSkipped(
                "This will fail on AppVeyor because it won't be able to find youtube-dl at the defaut path."
            );
        }

        Config::setOptions(['appName' => 'foo'], false);
        $config = Config::getInstance();
        $this->assertEquals($config->appName, 'foo');
    }

    /**
     * Test the setOptions function.
     *
     * @return void
     * @expectedException Exception
     */
    public function testSetOptionsWithBadYoutubedl()
    {
        Config::setOptions(['youtubedl' => 'foo']);
    }

    /**
     * Test the setOptions function.
     *
     * @return void
     * @expectedException Exception
     */
    public function testSetOptionsWithBadPython()
    {
        Config::setOptions(['python' => 'foo']);
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
        Config::setFile($this->getConfigFile());
        $config = Config::getInstance();
        $this->assertEquals($config->convert, true);
        putenv('CONVERT');
    }
}

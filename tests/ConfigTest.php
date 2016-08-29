<?php
/**
 * ConfigTest class
 */
namespace Alltube\Test;

use Alltube\Config;

/**
 * Unit tests for the Config class
 */
class ConfigTest extends \PHPUnit_Framework_TestCase
{

    private $config;

    protected function setUp()
    {
        $this->config = Config::getInstance('config_test.yml');
    }

    /**
     * Test the getInstance function
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

    public function testGetInstanceWithEnv()
    {
        putenv('CONVERT=1');
        Config::destroyInstance();
        $config = Config::getInstance('config_test.yml');
        $this->assertEquals($config->convert, true);
    }
}

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

    /**
     * Test the getInstance function
     *
     * @return void
     */
    public function testGetInstance()
    {
        putenv('CONVERT=1');
        $config = Config::getInstance();
        $this->assertEquals($config->convert, true);
        $this->assertInternalType('array', $config->curl_params);
        $this->assertInternalType('array', $config->params);
        $this->assertInternalType('string', $config->youtubedl);
        $this->assertInternalType('string', $config->python);
        $this->assertInternalType('string', $config->avconv);
        $this->assertInternalType('string', $config->rtmpdump);
    }
}

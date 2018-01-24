<?php
/**
 * VideoDownloadStubsTest class.
 */

namespace Alltube\Test;

use Alltube\Config;
use Alltube\VideoDownload;
use Mockery;
use phpmock\mockery\PHPMockery;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for the VideoDownload class.
 * They are in a separate file so they can safely replace PHP functions with stubs.
 */
class VideoDownloadStubsTest extends TestCase
{
    /**
     * VideoDownload instance.
     *
     * @var VideoDownload
     */
    private $download;

    /**
     * Config class instance.
     *
     * @var Config
     */
    private $config;

    /**
     * Video URL used in many tests.
     *
     * @var string
     */
    private $url;

    /**
     * Initialize properties used by test.
     */
    protected function setUp()
    {
        PHPMockery::mock('Alltube', 'popen');
        PHPMockery::mock('Alltube', 'fopen');

        if (PHP_OS == 'WINNT') {
            $configFile = 'config_test_windows.yml';
        } else {
            $configFile = 'config_test.yml';
        }
        $this->config = Config::getInstance('config/'.$configFile);
        $this->download = new VideoDownload($this->config);
        $this->url = 'https://www.youtube.com/watch?v=XJC9_JkzugE';
    }

    /**
     * Remove stubs.
     *
     * @return void
     */
    protected function tearDown()
    {
        Mockery::close();
    }

    /**
     * Test getAudioStream function with a buggy popen.
     *
     * @return void
     * @expectedException Exception
     */
    public function testGetAudioStreamWithPopenError()
    {
        $this->download->getAudioStream($this->url, 'best');
    }

    /**
     * Test getM3uStream function with a buggy popen.
     *
     * @return void
     * @expectedException Exception
     */
    public function testGetM3uStreamWithPopenError()
    {
        $this->download->getM3uStream($this->download->getJSON($this->url, 'best'));
    }

    /**
     * Test getRtmpStream function with a buggy popen.
     *
     * @return void
     * @expectedException Exception
     */
    public function testGetRtmpStreamWithPopenError()
    {
        $this->download->getRtmpStream($this->download->getJSON($this->url, 'best'));
    }

    /**
     * Test getRemuxStream function with a buggy popen.
     *
     * @return void
     * @expectedException Exception
     */
    public function testGetRemuxStreamWithPopenError()
    {
        $this->download->getRemuxStream([$this->url, $this->url]);
    }

    /**
     * Test getPlaylistArchiveStream function with a buggy popen.
     *
     * @return void
     * @expectedException Exception
     */
    public function testGetPlaylistArchiveStreamWithPopenError()
    {
        $video = $this->download->getJSON(
            'https://www.youtube.com/playlist?list=PLgdySZU6KUXL_8Jq5aUkyNV7wCa-4wZsC',
            'best'
        );
        $this->download->getPlaylistArchiveStream($video, 'best');
    }

    /**
     * Test getConvertedStream function with a buggy popen.
     *
     * @return void
     * @expectedException Exception
     */
    public function testGetConvertedStreamWithPopenError()
    {
        $this->download->getConvertedStream($this->url, 'best', 32, 'flv');
    }
}

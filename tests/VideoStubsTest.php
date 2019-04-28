<?php
/**
 * VideoStubsTest class.
 */

namespace Alltube\Test;

use Alltube\Video;
use Mockery;
use phpmock\mockery\PHPMockery;

/**
 * Unit tests for the Video class.
 * They are in a separate file so they can safely replace PHP functions with stubs.
 */
class VideoStubsTest extends BaseTest
{
    /**
     * Video URL used in many tests.
     *
     * @var Video
     */
    private $video;

    /**
     * Initialize properties used by test.
     */
    protected function setUp()
    {
        parent::setUp();

        PHPMockery::mock('Alltube', 'popen');
        PHPMockery::mock('Alltube', 'fopen');

        $this->video = new Video('https://www.youtube.com/watch?v=XJC9_JkzugE');
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
        $this->video->getAudioStream();
    }

    /**
     * Test getM3uStream function with a buggy popen.
     *
     * @return void
     * @expectedException Exception
     */
    public function testGetM3uStreamWithPopenError()
    {
        $this->video->getM3uStream();
    }

    /**
     * Test getRtmpStream function with a buggy popen.
     *
     * @return void
     * @expectedException Exception
     */
    public function testGetRtmpStreamWithPopenError()
    {
        $this->video->getRtmpStream();
    }

    /**
     * Test getRemuxStream function with a buggy popen.
     *
     * @return void
     * @expectedException Exception
     */
    public function testGetRemuxStreamWithPopenError()
    {
        $video = $this->video->withFormat('bestvideo+bestaudio');
        $video->getRemuxStream();
    }

    /**
     * Test getConvertedStream function with a buggy popen.
     *
     * @return void
     * @expectedException Exception
     */
    public function testGetConvertedStreamWithPopenError()
    {
        $this->video->getConvertedStream(32, 'flv');
    }
}

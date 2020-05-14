<?php

/**
 * VideoStubsTest class.
 */

namespace Alltube\Test;

use Alltube\Video;
use Mockery;
use phpmock\mockery\PHPMockery;
use Exception;

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
     * @throws Exception
     */
    protected function setUp(): void
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
    protected function tearDown(): void
    {
        Mockery::close();
    }

    /**
     * Test getAudioStream function with a buggy popen.
     *
     * @return void
     */
    public function testGetAudioStreamWithPopenError()
    {
        $this->expectException(Exception::class);
        $this->video->getAudioStream();
    }

    /**
     * Test getM3uStream function with a buggy popen.
     *
     * @return void
     */
    public function testGetM3uStreamWithPopenError()
    {
        $this->expectException(Exception::class);
        $this->video->getM3uStream();
    }

    /**
     * Test getRtmpStream function with a buggy popen.
     *
     * @return void
     */
    public function testGetRtmpStreamWithPopenError()
    {
        $this->expectException(Exception::class);
        $this->video->getRtmpStream();
    }

    /**
     * Test getRemuxStream function with a buggy popen.
     *
     * @return void
     */
    public function testGetRemuxStreamWithPopenError()
    {
        $this->expectException(Exception::class);
        $video = $this->video->withFormat('bestvideo+bestaudio');
        $video->getRemuxStream();
    }

    /**
     * Test getConvertedStream function with a buggy popen.
     *
     * @return void
     */
    public function testGetConvertedStreamWithPopenError()
    {
        $this->expectException(Exception::class);
        $this->video->getConvertedStream(32, 'flv');
    }
}

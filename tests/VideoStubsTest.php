<?php

/**
 * VideoStubsTest class.
 */

namespace Alltube\Test;

use Alltube\Exception\ConfigException;
use Alltube\Exception\DependencyException;
use Alltube\Library\Downloader;
use Alltube\Library\Exception\AlltubeLibraryException;
use Alltube\Library\Exception\PopenStreamException;
use Alltube\Library\Video;
use Mockery;
use phpmock\mockery\PHPMockery;
use SmartyException;

/**
 * Unit tests for the Video class.
 * They are in a separate file so they can safely replace PHP functions with stubs.
 *
 * @requires download
 */
class VideoStubsTest extends ContainerTest
{
    /**
     * Video used in many tests.
     *
     * @var Video
     */
    private $video;

    /**
     * Downloader instance used in tests.
     *
     * @var Downloader
     */
    private $downloader;

    /**
     * Initialize properties used by test.
     *
     * @throws ConfigException
     * @throws DependencyException
     * @throws SmartyException
     */
    protected function setUp(): void
    {
        parent::setUp();

        PHPMockery::mock('Alltube\Library', 'popen');
        PHPMockery::mock('Alltube\Library', 'fopen');

        $this->downloader = $this->container->get('config')->getDownloader();
        $this->video = $this->downloader->getVideo('https://www.youtube.com/watch?v=XJC9_JkzugE');
    }

    /**
     * Remove stubs.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        Mockery::close();
    }

    /**
     * Test getAudioStream function with a buggy popen.
     *
     * @return void
     * @throws AlltubeLibraryException
     */
    public function testGetAudioStreamWithPopenError()
    {
        $this->expectException(PopenStreamException::class);
        $this->downloader->getAudioStream($this->video);
    }

    /**
     * Test getM3uStream function with a buggy popen.
     *
     * @return void
     * @throws AlltubeLibraryException
     */
    public function testGetM3uStreamWithPopenError()
    {
        $this->expectException(PopenStreamException::class);
        $this->downloader->getM3uStream($this->video);
    }

    /**
     * Test getRtmpStream function with a buggy popen.
     *
     * @return void
     * @throws AlltubeLibraryException
     */
    public function testGetRtmpStreamWithPopenError()
    {
        $this->expectException(PopenStreamException::class);
        $this->downloader->getRtmpStream($this->video);
    }

    /**
     * Test getRemuxStream function with a buggy popen.
     *
     * @return void
     * @throws AlltubeLibraryException
     */
    public function testGetRemuxStreamWithPopenError()
    {
        $this->expectException(PopenStreamException::class);
        $video = $this->video->withFormat('bestvideo+bestaudio');
        $this->downloader->getRemuxStream($video);
    }

    /**
     * Test getConvertedStream function with a buggy popen.
     *
     * @return void
     * @throws AlltubeLibraryException
     */
    public function testGetConvertedStreamWithPopenError()
    {
        $this->expectException(PopenStreamException::class);
        $this->downloader->getConvertedStream($this->video, 32, 'flv');
    }
}

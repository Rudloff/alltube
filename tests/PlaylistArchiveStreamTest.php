<?php
/**
 * PlaylistArchiveStreamTest class.
 */

namespace Alltube\Test;

use Alltube\PlaylistArchiveStream;

/**
 * Unit tests for the ViewFactory class.
 */
class PlaylistArchiveStreamTest extends \PHPUnit_Framework_TestCase
{
    /**
     * PlaylistArchiveStream instance.
     *
     * @var PlaylistArchiveStream
     */
    private $stream;

    /**
     * Prepare tests.
     */
    protected function setUp()
    {
        $this->stream = new PlaylistArchiveStream();
    }

    /**
     * Test the stream_open() function.
     *
     * @return void
     */
    public function testStreamOpen()
    {
        $this->assertTrue($this->stream->stream_open('playlist://foo'));
    }

    /**
     * Test the stream_write() function.
     *
     * @return void
     */
    public function testStreamWrite()
    {
        $this->assertEquals(0, $this->stream->stream_write());
    }

    /**
     * Test the stream_stat() function.
     *
     * @return void
     */
    public function testStreamStat()
    {
        $this->assertEquals(['mode'=>4096], $this->stream->stream_stat());
    }

    /**
     * Test the stream_tell() function.
     *
     * @return void
     */
    public function testStreamTell()
    {
        $this->stream->stream_open('playlist://foo');
        $this->assertInternalType('int', $this->stream->stream_tell());
    }

    /**
     * Test the stream_seek() function.
     *
     * @return void
     */
    public function testStreamSeek()
    {
        $this->stream->stream_open('playlist://foo');
        $this->assertInternalType('bool', $this->stream->stream_seek(3));
    }

    /**
     * Test the stream_read() function.
     *
     * @return void
     */
    public function testStreamRead()
    {
        $this->stream->stream_open('playlist://BaW_jenozKc;BaW_jenozKc/worst');
        while (!$this->stream->stream_eof()) {
            $this->assertLessThanOrEqual(8192, strlen($this->stream->stream_read(8192)));
        }
    }

    /**
     * Test the stream_eof() function.
     *
     * @return void
     */
    public function testStreamEof()
    {
        $this->stream->stream_open('playlist://foo');
        $this->assertFalse($this->stream->stream_eof());
    }
}

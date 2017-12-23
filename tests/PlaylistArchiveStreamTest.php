<?php
/**
 * PlaylistArchiveStreamTest class.
 */

namespace Alltube\Test;

use Alltube\Config;
use Alltube\PlaylistArchiveStream;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for the ViewFactory class.
 */
class PlaylistArchiveStreamTest extends TestCase
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
        if (PHP_OS == 'WINNT') {
            $configFile = 'config_test_windows.yml';
        } else {
            $configFile = 'config_test.yml';
        }
        $this->stream = new PlaylistArchiveStream(Config::getInstance('config/'.$configFile));
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
        $this->assertEquals(['mode' => 4096], $this->stream->stream_stat());
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
            $result = $this->stream->stream_read(8192);
            $this->assertInternalType('string', $result);
            if (is_string($result)) {
                $this->assertLessThanOrEqual(8192, strlen($result));
            }
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

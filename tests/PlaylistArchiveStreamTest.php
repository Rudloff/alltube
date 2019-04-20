<?php
/**
 * PlaylistArchiveStreamTest class.
 */

namespace Alltube\Test;

use Alltube\Config;
use Alltube\PlaylistArchiveStream;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use stdClass;

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

        $entry = new stdClass();
        $entry->url = 'BaW_jenozKc';

        $video = new stdClass();
        $video->entries = [$entry, $entry];

        $this->stream = new PlaylistArchiveStream(Config::getInstance('config/'.$configFile), $video, 'worst');
    }

    /**
     * Clean variables used in tests.
     *
     * @return void
     */
    protected function tearDown()
    {
        $this->stream->close();
    }

    /**
     * Test the write() function.
     *
     * @return void
     * @expectedException RuntimeException
     */
    public function testWrite()
    {
        $this->stream->write('foo');
    }

    /**
     * Test the tell() function.
     *
     * @return void
     */
    public function testTell()
    {
        $this->assertInternalType('int', $this->stream->tell());
    }

    /**
     * Test the seek() function.
     *
     * @return void
     * @expectedException RuntimeException
     */
    public function testSeek()
    {
        $this->stream->seek(42);
    }

    /**
     * Test the read() function.
     *
     * @return void
     */
    public function testRead()
    {
        while (!$this->stream->eof()) {
            $result = $this->stream->read(8192);
            $this->assertInternalType('string', $result);
            if (is_string($result)) {
                $this->assertLessThanOrEqual(8192, strlen($result));
            }
        }
    }

    /**
     * Test the eof() function.
     *
     * @return void
     */
    public function testEof()
    {
        $this->assertFalse($this->stream->eof());
    }

    /**
     * Test the getSize() function.
     *
     * @return void
     */
    public function testGetSize()
    {
        $this->assertNull($this->stream->getSize());
    }

    /**
     * Test the isSeekable() function.
     *
     * @return void
     */
    public function testIsSeekable()
    {
        $this->assertFalse($this->stream->isSeekable());
    }

    /**
     * Test the rewind() function.
     *
     * @return void
     * @expectedException RuntimeException
     */
    public function testRewind()
    {
        $this->stream->rewind();
    }

    /**
     * Test the isWritable() function.
     *
     * @return void
     */
    public function testIsWritable()
    {
        $this->assertFalse($this->stream->isWritable());
    }

    /**
     * Test the isReadable() function.
     *
     * @return void
     */
    public function testIsReadable()
    {
        $this->assertTrue($this->stream->isReadable());
    }

    /**
     * Test the getContents() function.
     *
     * @return void
     */
    public function testGetContents()
    {
        $this->assertInternalType('string', $this->stream->getContents());
    }

    /**
     * Test the getMetadata() function.
     *
     * @return void
     */
    public function testGetMetadata()
    {
        $this->assertNull($this->stream->getMetadata());
    }

    /**
     * Test the detach() function.
     *
     * @return void
     */
    public function testDetach()
    {
        $this->assertInternalType('resource', $this->stream->detach());
    }

    /**
     * Test the __toString() function.
     *
     * @return void
     */
    public function testToString()
    {
        $this->assertInternalType('string', $this->stream->__toString());
        $this->assertInternalType('string', (string) $this->stream);
    }
}

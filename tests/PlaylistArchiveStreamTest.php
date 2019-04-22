<?php
/**
 * PlaylistArchiveStreamTest class.
 */

namespace Alltube\Test;

use Alltube\Stream\PlaylistArchiveStream;
use Alltube\Video;

/**
 * Unit tests for the ViewFactory class.
 */
class PlaylistArchiveStreamTest extends BaseTest
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
        parent::setUp();

        $video = new Video('https://www.youtube.com/playlist?list=PL1j4Ff8cAqPu5iowaeUAY8lRgkfT4RybJ');

        $this->stream = new PlaylistArchiveStream($video);
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
     */
    public function testWrite()
    {
        $this->assertNull($this->stream->write('foo'));
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
     */
    public function testSeek()
    {
        $this->stream->write('foobar');
        $this->stream->seek(3);
        $this->assertEquals(3, $this->stream->tell());
    }

    /**
     * Test the read() function.
     *
     * @return void
     */
    public function testRead()
    {
        $result = $this->stream->read(8192);
        $this->assertInternalType('string', $result);
        $this->assertLessThanOrEqual(8192, strlen($result));
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
        $this->assertTrue($this->stream->isSeekable());
    }

    /**
     * Test the rewind() function.
     *
     * @return void
     */
    public function testRewind()
    {
        $this->stream->rewind();
        $this->assertEquals(0, $this->stream->tell());
    }

    /**
     * Test the isWritable() function.
     *
     * @return void
     */
    public function testIsWritable()
    {
        $this->assertTrue($this->stream->isWritable());
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
        $this->assertInternalType('array', $this->stream->getMetadata());
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

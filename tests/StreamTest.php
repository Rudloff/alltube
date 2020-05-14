<?php

/**
 * StreamTest class.
 */

namespace Alltube\Test;

use Psr\Http\Message\StreamInterface;
use RuntimeException;

/**
 * Abstract class used by the stream tests.
 */
abstract class StreamTest extends BaseTest
{
    /**
     * Stream instance.
     * @var StreamInterface
     */
    protected $stream;

    /**
     * Clean variables used in tests.
     *
     * @return void
     */
    protected function tearDown(): void
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
        if ($this->stream->isWritable()) {
            $this->assertIsInt($this->stream->write('foo'));
        } else {
            $this->expectException(RuntimeException::class);
            $this->stream->write('foo');
        }
    }

    /**
     * Test the tell() function.
     *
     * @return void
     */
    public function testTell()
    {
        $this->assertIsInt($this->stream->tell());
    }

    /**
     * Test the seek() function.
     *
     * @return void
     */
    public function testSeek()
    {
        if ($this->stream->isSeekable()) {
            if ($this->stream->isWritable()) {
                // We might need some data.
                $this->stream->write('foobar');
            }

            $this->stream->seek(3);
            $this->assertEquals(3, $this->stream->tell());
        } else {
            $this->expectException(RuntimeException::class);
            $this->stream->seek(3);
        }
    }

    /**
     * Test the read() function.
     *
     * @return void
     */
    public function testRead()
    {
        $result = $this->stream->read(8192);
        $this->assertIsString($result);
        $this->assertLessThanOrEqual(8192, strlen($result));
    }

    /**
     * Test the read() function.
     *
     * @return void
     */
    public function testReadEntireStream()
    {
        $this->markTestIncomplete('Can we test the whole logic without reading the whole stream?');
    }

    /**
     * Test the eof() function.
     *
     * @return void
     */
    public function testEof()
    {
        $this->assertIsBool($this->stream->eof());
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
        $this->assertIsBool($this->stream->isSeekable());
    }

    /**
     * Test the rewind() function.
     *
     * @return void
     */
    public function testRewind()
    {
        if ($this->stream->isSeekable()) {
            if ($this->stream->isWritable()) {
                // We might need some data.
                $this->stream->write('foobar');
            }

            $this->stream->rewind();
            $this->assertEquals(0, $this->stream->tell());
        } else {
            $this->expectException(RuntimeException::class);
            $this->stream->rewind();
        }
    }

    /**
     * Test the isWritable() function.
     *
     * @return void
     */
    public function testIsWritable()
    {
        $this->assertIsBool($this->stream->isWritable());
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
        $this->assertIsString($this->stream->getContents());
    }

    /**
     * Test the getMetadata() function.
     *
     * @return void
     */
    public function testGetMetadata()
    {
        $this->assertIsArray($this->stream->getMetadata());
    }

    /**
     * Test the getMetadata() function.
     *
     * @return void
     */
    public function testGetMetadataWithKey()
    {
        $this->assertIsString($this->stream->getMetadata('stream_type'));
        $this->assertIsString($this->stream->getMetadata('mode'));
        $this->assertIsBool($this->stream->getMetadata('seekable'));
        $this->assertNull($this->stream->getMetadata('foo'));
    }

    /**
     * Test the detach() function.
     *
     * @return void
     */
    public function testDetach()
    {
        $this->assertIsResource($this->stream->detach());
    }

    /**
     * Test the __toString() function.
     *
     * @return void
     */
    public function testToString()
    {
        $this->assertIsString($this->stream->__toString());
        $this->assertIsString((string) $this->stream);
    }
}

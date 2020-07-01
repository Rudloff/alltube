<?php

/**
 * YoutubeChunkStream class.
 */

namespace Alltube\Stream;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * This is a wrapper around GuzzleHttp\Psr7\Stream.
 * It is required because Youtube HTTP responses are buggy if we try to read further than the end of the response.
 */
class YoutubeChunkStream implements StreamInterface
{
    /**
     * HTTP response containing the video chunk.
     *
     * @var ResponseInterface
     */
    private $response;

    /**
     * YoutubeChunkStream constructor.
     *
     * @param ResponseInterface $response HTTP response containing the video chunk
     */
    public function __construct(ResponseInterface $response)
    {
        $this->response = $response;
    }

    /**
     * Read data from the stream.
     *
     * @param int $length Read up to $length bytes from the object and return
     *
     * @return string
     */
    public function read($length)
    {
        $size = intval($this->response->getHeader('Content-Length')[0]);
        if ($size - $this->tell() < $length) {
            // Don't try to read further than the end of the stream.
            $length = $size - $this->tell();
        }

        return $this->response->getBody()->read($length);
    }

    /**
     * Reads all data from the stream into a string, from the beginning to end.
     */
    public function __toString()
    {
        return (string)$this->response->getBody();
    }

    /**
     * Closes the stream and any underlying resources.
     *
     * @return void
     */
    public function close()
    {
        $this->response->getBody()->close();
    }

    /**
     * Separates any underlying resources from the stream.
     *
     * @return resource|null
     */
    public function detach()
    {
        return $this->response->getBody()->detach();
    }

    /**
     * Get the size of the stream if known.
     *
     * @return int|null
     */
    public function getSize()
    {
        return $this->response->getBody()->getSize();
    }

    /**
     * Returns the current position of the file read/write pointer.
     *
     * @return int
     */
    public function tell()
    {
        return $this->response->getBody()->tell();
    }

    /**
     * Returns true if the stream is at the end of the stream.
     *
     * @return bool
     */
    public function eof()
    {
        return $this->response->getBody()->eof();
    }

    /**
     * Returns whether or not the stream is seekable.
     *
     * @return bool
     */
    public function isSeekable()
    {
        return $this->response->getBody()->isSeekable();
    }

    /**
     * Seek to a position in the stream.
     *
     * @param int $offset Stream offset
     * @param int $whence Specifies how the cursor position will be calculated
     *
     * @return void
     */
    public function seek($offset, $whence = SEEK_SET)
    {
        $this->response->getBody()->seek($offset, $whence);
    }

    /**
     * Seek to the beginning of the stream.
     *
     * @return void
     */
    public function rewind()
    {
        $this->response->getBody()->rewind();
    }

    /**
     * Returns whether or not the stream is writable.
     *
     * @return bool
     */
    public function isWritable()
    {
        return $this->response->getBody()->isWritable();
    }

    /**
     * Write data to the stream.
     *
     * @param string $string The string that is to be written
     *
     * @return mixed
     */
    public function write($string)
    {
        return $this->response->getBody()->write($string);
    }

    /**
     * Returns whether or not the stream is readable.
     *
     * @return bool
     */
    public function isReadable()
    {
        return $this->response->getBody()->isReadable();
    }

    /**
     * Returns the remaining contents in a string.
     *
     * @return string
     */
    public function getContents()
    {
        return $this->response->getBody()->getContents();
    }

    /**
     * Get stream metadata as an associative array or retrieve a specific key.
     *
     * @param string $key Specific metadata to retrieve.
     *
     * @return array|mixed|null
     */
    public function getMetadata($key = null)
    {
        return $this->response->getBody()->getMetadata($key);
    }
}

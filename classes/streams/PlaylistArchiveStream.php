<?php

/**
 * PlaylistArchiveStream class.
 */

namespace Alltube\Stream;

use Alltube\Exception\EmptyUrlException;
use Alltube\Exception\PasswordException;
use Alltube\Video;
use Barracuda\ArchiveStream\ZipArchive;
use Psr\Http\Message\StreamInterface;

/**
 * Class used to create a Zip archive from playlists and stream it to the browser.
 *
 * @link https://github.com/php-fig/http-message/blob/master/src/StreamInterface.php
 */
class PlaylistArchiveStream extends ZipArchive implements StreamInterface
{
    /**
     * videos to add in the archive.
     *
     * @var Video[]
     */
    private $videos = [];

    /**
     * Stream used to store data before it is sent to the browser.
     *
     * @var resource
     */
    private $buffer;

    /**
     * Current video being streamed to the archive.
     *
     * @var StreamInterface
     */
    protected $curVideoStream;

    /**
     * True if the archive is complete.
     *
     * @var bool
     */
    private $isComplete = false;

    /**
     * PlaylistArchiveStream constructor.
     *
     * We don't call the parent constructor because it messes up the output buffering.
     *
     * @param Video $video Video/playlist to download
     * @noinspection PhpMissingParentConstructorInspection
     */
    public function __construct(Video $video)
    {
        $buffer = fopen('php://temp', 'r+');
        if ($buffer !== false) {
            $this->buffer = $buffer;
        }
        foreach ($video->entries as $entry) {
            $this->videos[] = new Video($entry->url);
        }
    }

    /**
     * Add data to the archive.
     *
     * @param string $data Data
     *
     * @return void
     */
    protected function send($data)
    {
        $pos = $this->tell();

        // Add data to the end of the buffer.
        $this->seek(0, SEEK_END);
        $this->write($data);
        if ($pos !== false) {
            // Rewind so that read() can later read this data.
            $this->seek($pos);
        }
    }

    /**
     * Write data to the stream.
     *
     * @param string $string The string that is to be written
     *
     * @return int|false
     */
    public function write($string)
    {
        return fwrite($this->buffer, $string);
    }

    /**
     * Get the size of the stream if known.
     *
     * @return int|null
     */
    public function getSize()
    {
        return null;
    }

    /**
     * Returns whether or not the stream is seekable.
     *
     * @return bool
     */
    public function isSeekable()
    {
        return true;
    }

    /**
     * Seek to the beginning of the stream.
     *
     * @return void
     */
    public function rewind()
    {
        rewind($this->buffer);
    }

    /**
     * Returns whether or not the stream is writable.
     *
     * @return bool
     */
    public function isWritable()
    {
        return true;
    }

    /**
     * Returns whether or not the stream is readable.
     *
     * @return bool
     */
    public function isReadable()
    {
        return true;
    }

    /**
     * Returns the remaining contents in a string.
     *
     * @return string|false
     */
    public function getContents()
    {
        return stream_get_contents($this->buffer);
    }

    /**
     * Get stream metadata as an associative array or retrieve a specific key.
     *
     * @param string $key string $key Specific metadata to retrieve.
     *
     * @return array|mixed|null
     */
    public function getMetadata($key = null)
    {
        $meta = stream_get_meta_data($this->buffer);

        if (!isset($key)) {
            return $meta;
        }

        if (isset($meta[$key])) {
            return $meta[$key];
        }

        return null;
    }

    /**
     * Separates any underlying resources from the stream.
     *
     * @return resource
     */
    public function detach()
    {
        $stream = $this->buffer;
        $this->close();

        return $stream;
    }

    /**
     * Reads all data from the stream into a string, from the beginning to end.
     *
     * @return string
     */
    public function __toString()
    {
        $this->rewind();

        return strval($this->getContents());
    }

    /**
     * Returns the current position of the file read/write pointer.
     *
     * @return int|false
     */
    public function tell()
    {
        return ftell($this->buffer);
    }

    /**
     * Seek to a position in the stream.
     *
     * @param int $offset Offset
     * @param int $whence Specifies how the cursor position will be calculated
     *
     * @return void
     */
    public function seek($offset, $whence = SEEK_SET)
    {
        fseek($this->buffer, $offset, $whence);
    }

    /**
     * Returns true if the stream is at the end of the archive.
     *
     * @return bool
     */
    public function eof()
    {
        return $this->isComplete && feof($this->buffer);
    }

    /**
     * Start streaming a new video.
     *
     * @param Video $video Video to stream
     *
     * @return void
     * @throws PasswordException
     * @throws EmptyUrlException
     */
    protected function startVideoStream(Video $video)
    {
        $response = $video->getHttpResponse();

        $this->curVideoStream = $response->getBody();
        $contentLengthHeaders = $response->getHeader('Content-Length');

        $this->init_file_stream_transfer(
            $video->getFilename(),
            intval($contentLengthHeaders[0])
        );
    }

    /**
     * Read data from the stream.
     *
     * @param int $count Number of bytes to read
     *
     * @return string|false
     * @throws EmptyUrlException
     * @throws PasswordException
     */
    public function read($count)
    {
        // If the archive is complete, we only read the remaining buffer.
        if (!$this->isComplete) {
            if (isset($this->curVideoStream)) {
                if ($this->curVideoStream->eof()) {
                    // Stop streaming the current video.
                    $this->complete_file_stream();

                    $video = next($this->videos);
                    if ($video) {
                        // Start streaming the next video.
                        $this->startVideoStream($video);
                    } else {
                        // No video left.
                        $this->finish();
                        $this->isComplete = true;
                    }
                } else {
                    // Continue streaming the current video.
                    $this->stream_file_part($this->curVideoStream->read($count));
                }
            } else {
                // Start streaming the first video.
                $this->startVideoStream(current($this->videos));
            }
        }

        return fread($this->buffer, $count);
    }

    /**
     * Closes the stream and any underlying resources.
     *
     * @return void
     */
    public function close()
    {
        if (is_resource($this->buffer)) {
            fclose($this->buffer);
        }
        if (isset($this->curVideoStream)) {
            $this->curVideoStream->close();
        }
    }
}

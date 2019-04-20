<?php
/**
 * PlaylistArchiveStream class.
 */

namespace Alltube;

use Barracuda\ArchiveStream\TarArchive;
use GuzzleHttp\Client;
use Psr\Http\Message\StreamInterface;
use RuntimeException;
use stdClass;

/**
 * Class used to create a Tar archive from playlists and stream it to the browser.
 *
 * @link https://github.com/php-fig/http-message/blob/master/src/StreamInterface.php
 */
class PlaylistArchiveStream extends TarArchive implements StreamInterface
{
    /**
     * videos to add in the archive.
     *
     * @var PlaylistArchiveVideo[]
     */
    private $videos = [];

    /**
     * Stream used to store data before it is sent to the browser.
     *
     * @var resource
     */
    private $buffer;

    /**
     * Guzzle client.
     *
     * @var Client
     */
    private $client;

    /**
     * VideoDownload instance.
     *
     * @var VideoDownload
     */
    private $download;

    /**
     * Current video being streamed to the archive.
     *
     * @var int
     */
    private $curVideo;

    /**
     * Video format to download.
     *
     * @var string
     */
    private $format;

    /**
     * PlaylistArchiveStream constructor.
     *
     * @param Config $config Config instance.
     * @param stdClass $video  Video object returned by youtube-dl
     * @param string   $format Requested format
     */
    public function __construct(Config $config, stdClass $video, $format)
    {
        $this->client = new Client();
        $this->download = new VideoDownload($config);

        $this->format = $format;
        $buffer = fopen('php://temp', 'r+');
        if ($buffer !== false) {
            $this->buffer = $buffer;
        }
        foreach ($video->entries as $entry) {
            $this->videos[] = new PlaylistArchiveVideo($entry->url);
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
        $pos = ftell($this->buffer);

        // Add data to the buffer.
        fwrite($this->buffer, $data);
        if ($pos !== false) {
            // Rewind so that read() can later read this data.
            fseek($this->buffer, $pos);
        }
    }

    /**
     * Write data to the stream.
     *
     * @param string $string The string that is to be written.
     *
     * @return int
     */
    public function write($string)
    {
        throw new RuntimeException('This stream is not writeable.');
    }

    /**
     * Get the size of the stream if known.
     *
     * @return null
     */
    public function getSize()
    {
        return null;
    }

    /**
     * Returns whether or not the stream is seekable.
     *
     * @return boolean
     */
    public function isSeekable()
    {
        return false;
    }

    /**
     * Seek to the beginning of the stream.
     *
     * @return void
     */
    public function rewind()
    {
        throw new RuntimeException('This stream is not seekable.');
    }

    /**
     * Returns whether or not the stream is writable.
     *
     * @return boolean
     */
    public function isWritable()
    {
        return false;
    }

    /**
     * Returns whether or not the stream is readable.
     *
     * @return boolean
     */
    public function isReadable()
    {
        return true;
    }

    /**
     * Returns the remaining contents in a string.
     *
     * @return string
     */
    public function getContents()
    {
        return stream_get_contents($this->buffer);
    }

    /**
     * Get stream metadata as an associative array or retrieve a specific key.
     *
     * @param  string $key string $key Specific metadata to retrieve.
     *
     * @return null
     */
    public function getMetadata($key = null)
    {
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
        $string = '';

        foreach ($this->videos as $file) {
            $string .= $file->url;
        }

        return $string;
    }

    /**
     * Returns the current position of the file read/write pointer
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
        throw new RuntimeException('This stream is not seekable.');
    }

    /**
     * Returns true if the stream is at the end of the stream.
     *
     * @return bool
     */
    public function eof()
    {
        foreach ($this->videos as $file) {
            if (!$file->complete) {
                return false;
            }
        }

        return true;
    }

    /**
     * Read data from the stream.
     *
     * @param int $count Number of bytes to read
     *
     * @return string|false
     */
    public function read($count)
    {
        if (isset($this->curVideo)) {
            if (isset($this->curVideo->stream)) {
                if (!$this->curVideo->stream->eof()) {
                    $this->stream_file_part($this->curVideo->stream->read($count));
                } elseif (!$this->curVideo->complete) {
                    $this->complete_file_stream();
                    $this->curVideo->complete = true;
                } else {
                    $this->curVideo = next($this->videos);
                }
            } else {
                $urls = $this->download->getURL($this->curVideo->url, $this->format);
                $response = $this->client->request('GET', $urls[0], ['stream' => true]);

                $contentLengthHeaders = $response->getHeader('Content-Length');
                $this->init_file_stream_transfer(
                    $this->download->getFilename($this->curVideo->url, $this->format),
                    $contentLengthHeaders[0]
                );

                $this->curVideo->stream = $response->getBody();
            }
        } else {
            $this->curVideo = current($this->videos);
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
        foreach ($this->videos as $file) {
            if (is_resource($file->stream)) {
                fclose($file->stream);
            }
        }
    }
}

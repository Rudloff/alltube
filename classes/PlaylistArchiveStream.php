<?php
/**
 * PlaylistArchiveStream class.
 *
 * @codingStandardsIgnoreFile
 */

namespace Alltube;

use Barracuda\ArchiveStream\TarArchive;

/**
 * Class used to create a Tar archive from playlists and stream it to the browser.
 *
 * @link http://php.net/manual/en/class.streamwrapper.php
 */
class PlaylistArchiveStream extends TarArchive
{
    /**
     * Files to add in the archive.
     *
     * @var array[]
     */
    private $files;

    /**
     * Stream used to store data before it is sent to the browser.
     *
     * @var resource
     */
    private $buffer;

    /**
     * Guzzle client.
     *
     * @var \GuzzleHttp\Client
     */
    private $client;

    /**
     * VideoDownload instance.
     *
     * @var VideoDownload
     */
    private $download;

    /**
     * Current file position in $files array.
     *
     * @var int
     */
    private $curFile = 0;

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
     */
    public function __construct(Config $config = null)
    {
        $this->client = new \GuzzleHttp\Client();
        $this->download = new VideoDownload($config);
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
        fwrite($this->buffer, $data);
        if ($pos !== false) {
            fseek($this->buffer, $pos);
        }
    }

    /**
     * Called when fopen() is used on the stream.
     *
     * @param string $path Playlist path (should be playlist://url1;url2;.../format)
     *
     * @return bool
     */
    public function stream_open($path)
    {
        $this->format = ltrim(parse_url($path, PHP_URL_PATH), '/');
        $buffer = fopen('php://temp', 'r+');
        if ($buffer !== false) {
            $this->buffer = $buffer;
        }
        foreach (explode(';', parse_url($path, PHP_URL_HOST)) as $url) {
            $this->files[] = [
                'url'         => urldecode($url),
                'headersSent' => false,
                'complete'    => false,
                'stream'      => null,
            ];
        }

        return true;
    }

    /**
     * Called when fwrite() is used on the stream.
     *
     * @return int
     */
    public function stream_write()
    {
        //We don't support writing to a stream
        return 0;
    }

    /**
     * Called when fstat() is used on the stream.
     *
     * @return array
     */
    public function stream_stat()
    {
        //We need this so Slim won't try to get the size of the stream
        return [
            'mode' => 0010000,
        ];
    }

    /**
     * Called when ftell() is used on the stream.
     *
     * @return int|false
     */
    public function stream_tell()
    {
        return ftell($this->buffer);
    }

    /**
     * Called when fseek() is used on the stream.
     *
     * @param int $offset Offset
     *
     * @return bool
     */
    public function stream_seek($offset)
    {
        return fseek($this->buffer, $offset) == 0;
    }

    /**
     * Called when feof() is used on the stream.
     *
     * @return bool
     */
    public function stream_eof()
    {
        foreach ($this->files as $file) {
            if (!$file['complete']) {
                return false;
            }
        }

        return true;
    }

    /**
     * Called when fread() is used on the stream.
     *
     * @param int $count Number of bytes to read
     *
     * @return string|false
     */
    public function stream_read($count)
    {
        if (!$this->files[$this->curFile]['headersSent']) {
            $urls = $this->download->getUrl($this->files[$this->curFile]['url'], $this->format);
            $response = $this->client->request('GET', $urls[0], ['stream' => true]);

            $contentLengthHeaders = $response->getHeader('Content-Length');
            $this->init_file_stream_transfer(
                $this->download->getFilename($this->files[$this->curFile]['url'], $this->format),
                $contentLengthHeaders[0]
            );

            $this->files[$this->curFile]['headersSent'] = true;
            $this->files[$this->curFile]['stream'] = $response->getBody();
        } elseif (!$this->files[$this->curFile]['stream']->eof()) {
            $this->stream_file_part($this->files[$this->curFile]['stream']->read($count));
        } elseif (!$this->files[$this->curFile]['complete']) {
            $this->complete_file_stream();
            $this->files[$this->curFile]['complete'] = true;
        } elseif (isset($this->files[$this->curFile])) {
            $this->curFile += 1;
        }

        return fread($this->buffer, $count);
    }
}

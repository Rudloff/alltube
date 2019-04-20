<?php
/**
 * PlaylistArchiveVideo class.
 */

namespace Alltube;

use Barracuda\ArchiveStream\TarArchive;
use GuzzleHttp\Client;
use Psr\Http\Message\StreamInterface;
use RuntimeException;
use stdClass;

/**
 * Video streamed to a PlaylistArchiveStream.
 */
class PlaylistArchiveVideo
{
    /**
     * Video page URL.
     *
     * @var string
     */
    public $url;

    /**
     * Has the video been streaded entirely ?
     *
     * @var bool
     */
    public $complete = false;

    /**
     * popen stream containing the video.
     *
     * @var resource
     */
    public $stream;

    /**
     * PlaylistArchiveVideo constructor.
     *
     * @param string $url Video page URL
     */
    public function __construct($url)
    {
        $this->url = $url;
    }
}

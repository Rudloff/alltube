<?php
/**
 * PlaylistArchiveVideo class.
 */

namespace Alltube;

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
     * Has the video been streamed entirely ?
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

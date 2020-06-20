<?php

/**
 * ConvertedPlaylistArchiveStreamTest class.
 */

namespace Alltube\Test;

use Alltube\Config;
use Alltube\Exception\ConfigException;
use Alltube\Stream\ConvertedPlaylistArchiveStream;

/**
 * Unit tests for the ConvertedPlaylistArchiveStream class.
 * @requires download
 */
class ConvertedPlaylistArchiveStreamTest extends StreamTest
{
    /**
     * Prepare tests.
     * @throws ConfigException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $config = Config::getInstance();
        $downloader = $config->getDownloader();
        $video = $downloader->getVideo('https://www.youtube.com/playlist?list=PL1j4Ff8cAqPu5iowaeUAY8lRgkfT4RybJ');

        $this->stream = new ConvertedPlaylistArchiveStream($downloader, $video);
    }
}

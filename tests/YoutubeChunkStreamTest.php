<?php

/**
 * YoutubeChunkStreamTest class.
 */

namespace Alltube\Test;

use Alltube\Config;
use Alltube\Exception\ConfigException;
use Alltube\Library\Exception\AlltubeLibraryException;
use Alltube\Stream\YoutubeChunkStream;

/**
 * Unit tests for the YoutubeChunkStream class.
 * @requires download
 */
class YoutubeChunkStreamTest extends StreamTest
{
    /**
     * Prepare tests.
     * @throws ConfigException
     * @throws AlltubeLibraryException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $config = Config::getInstance();
        $downloader = $config->getDownloader();
        $video = $downloader->getVideo('https://www.youtube.com/watch?v=dQw4w9WgXcQ');

        $this->stream = new YoutubeChunkStream($downloader->getHttpResponse($video));
    }
}

<?php

/**
 * YoutubeChunkStreamTest class.
 */

namespace Alltube\Test;

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
     * @throws AlltubeLibraryException
     * @throws ConfigException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $video = $this->downloader->getVideo('https://www.youtube.com/watch?v=dQw4w9WgXcQ');

        $this->stream = new YoutubeChunkStream($this->downloader->getHttpResponse($video));
    }
}

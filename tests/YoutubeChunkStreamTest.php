<?php

/**
 * YoutubeChunkStreamTest class.
 */

namespace Alltube\Test;

use Alltube\Stream\YoutubeChunkStream;
use Alltube\Video;

/**
 * Unit tests for the YoutubeChunkStream class.
 * @requires download
 */
class YoutubeChunkStreamTest extends StreamTest
{
    /**
     * Prepare tests.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $video = new Video('https://www.youtube.com/watch?v=dQw4w9WgXcQ');

        $this->stream = new YoutubeChunkStream($video->getHttpResponse());
    }
}

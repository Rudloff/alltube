<?php
/**
 * YoutubeStreamTest class.
 */

namespace Alltube\Test;

use Alltube\Stream\YoutubeStream;
use Alltube\Video;

/**
 * Unit tests for the YoutubeStream class.
 */
class YoutubeStreamTest extends StreamTest
{
    /**
     * Prepare tests.
     */
    protected function setUp()
    {
        parent::setUp();

        $video = new Video('https://www.youtube.com/watch?v=dQw4w9WgXcQ', '135');

        $this->stream = new YoutubeStream($video);
    }
}

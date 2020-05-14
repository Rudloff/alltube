<?php

/**
 * PlaylistArchiveStreamTest class.
 */

namespace Alltube\Test;

use Alltube\Stream\PlaylistArchiveStream;
use Alltube\Video;
use Exception;

/**
 * Unit tests for the PlaylistArchiveStream class.
 * @requires download
 */
class PlaylistArchiveStreamTest extends StreamTest
{
    /**
     * Prepare tests.
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        $video = new Video('https://www.youtube.com/playlist?list=PL1j4Ff8cAqPu5iowaeUAY8lRgkfT4RybJ');

        $this->stream = new PlaylistArchiveStream($video);
    }
}

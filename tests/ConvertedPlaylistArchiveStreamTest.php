<?php

/**
 * ConvertedPlaylistArchiveStreamTest class.
 */

namespace Alltube\Test;

use Alltube\Stream\ConvertedPlaylistArchiveStream;
use Alltube\Video;

/**
 * Unit tests for the ConvertedPlaylistArchiveStream class.
 * @requires download
 */
class ConvertedPlaylistArchiveStreamTest extends StreamTest
{
    /**
     * Prepare tests.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $video = new Video('https://www.youtube.com/playlist?list=PL1j4Ff8cAqPu5iowaeUAY8lRgkfT4RybJ');

        $this->stream = new ConvertedPlaylistArchiveStream($video);
    }
}

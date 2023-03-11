<?php

/**
 * ConvertedPlaylistArchiveStreamTest class.
 */

namespace Alltube\Test;

use Alltube\Exception\ConfigException;
use Alltube\Exception\DependencyException;
use Alltube\Stream\ConvertedPlaylistArchiveStream;
use SmartyException;

/**
 * Unit tests for the ConvertedPlaylistArchiveStream class.
 * @requires download
 */
class ConvertedPlaylistArchiveStreamTest extends StreamTest
{
    /**
     * Prepare tests.
     *
     * @throws ConfigException
     * @throws DependencyException
     * @throws SmartyException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $video = $this->downloader->getVideo(
            'https://www.youtube.com/playlist?list=PL1j4Ff8cAqPu5iowaeUAY8lRgkfT4RybJ'
        );

        $this->stream = new ConvertedPlaylistArchiveStream($this->downloader, $video);
    }
}

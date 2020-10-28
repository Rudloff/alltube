<?php

/**
 * YoutubeChunkStreamTest class.
 */

namespace Alltube\Test;

use Alltube\Exception\ConfigException;
use Alltube\Exception\DependencyException;
use Alltube\Library\Exception\AlltubeLibraryException;
use Alltube\Stream\YoutubeChunkStream;
use SmartyException;

/**
 * Unit tests for the YoutubeChunkStream class.
 * @requires download
 */
class YoutubeChunkStreamTest extends StreamTest
{
    /**
     * Prepare tests.
     *
     * @throws AlltubeLibraryException
     * @throws ConfigException
     * @throws DependencyException
     * @throws SmartyException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $video = $this->downloader->getVideo('https://www.youtube.com/watch?v=dQw4w9WgXcQ');

        $this->stream = new YoutubeChunkStream($this->downloader->getHttpResponse($video));
    }
}

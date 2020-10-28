<?php

/**
 * YoutubeStreamTest class.
 */

namespace Alltube\Test;

use Alltube\Exception\ConfigException;
use Alltube\Exception\DependencyException;
use Alltube\Library\Exception\AlltubeLibraryException;
use Alltube\Stream\YoutubeStream;
use SmartyException;

/**
 * Unit tests for the YoutubeStream class.
 * @requires download
 */
class YoutubeStreamTest extends StreamTest
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

        $video = $this->downloader->getVideo('https://www.youtube.com/watch?v=dQw4w9WgXcQ', '135');

        $this->stream = new YoutubeStream($this->downloader, $video);
    }

    /**
     * Test the getMetadata() function.
     *
     * @return void
     */
    public function testGetMetadataWithKey()
    {
        $this->assertNull($this->stream->getMetadata('foo'));
    }

    /**
     * Test the detach() function.
     *
     * @return void
     */
    public function testDetach()
    {
        $this->assertNull($this->stream->detach());
    }
}

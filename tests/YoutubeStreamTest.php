<?php

/**
 * YoutubeStreamTest class.
 */

namespace Alltube\Test;

use Alltube\Stream\YoutubeStream;
use Alltube\Video;
use Exception;

/**
 * Unit tests for the YoutubeStream class.
 * @requires download
 */
class YoutubeStreamTest extends StreamTest
{
    /**
     * Prepare tests.
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        $video = new Video('https://www.youtube.com/watch?v=dQw4w9WgXcQ', '135');

        $this->stream = new YoutubeStream($video);
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

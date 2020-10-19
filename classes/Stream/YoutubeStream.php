<?php

/**
 * YoutubeStream class.
 */

namespace Alltube\Stream;

use Alltube\Library\Downloader;
use Alltube\Library\Exception\AlltubeLibraryException;
use Alltube\Library\Video;
use GuzzleHttp\Psr7\AppendStream;

/**
 * Stream that downloads a video in chunks.
 * This is required because Youtube throttles the download speed on chunks larger than 10M.
 */
class YoutubeStream extends AppendStream
{
    /**
     * YoutubeStream constructor.
     *
     * @param Downloader $downloader Downloader object
     * @param Video $video Video to stream
     * @throws AlltubeLibraryException
     */
    public function __construct(Downloader $downloader, Video $video)
    {
        parent::__construct();

        $stream = $downloader->getHttpResponse($video);
        $contentLenghtHeader = $stream->getHeader('Content-Length');
        $rangeStart = 0;

        while ($rangeStart < $contentLenghtHeader[0]) {
            $rangeEnd = $rangeStart + $video->downloader_options->http_chunk_size;
            if ($rangeEnd >= $contentLenghtHeader[0]) {
                $rangeEnd = intval($contentLenghtHeader[0]) - 1;
            }
            $response = $downloader->getHttpResponse($video, ['Range' => 'bytes=' . $rangeStart . '-' . $rangeEnd]);
            $this->addStream(new YoutubeChunkStream($response));
            $rangeStart = $rangeEnd + 1;
        }
    }
}

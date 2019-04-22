<?php
/**
 * YoutubeStream class.
 */

namespace Alltube\Stream;

use Alltube\Video;
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
     * @param Video $video Video to stream
     */
    public function __construct(Video $video)
    {
        parent::__construct();

        $stream = $video->getHttpResponse();
        $fileSize = $stream->getHeader('Content-Length');
        $curSize = 0;
        while ($curSize < $fileSize[0]) {
            $newSize = $curSize + $video->downloader_options->http_chunk_size;
            if ($newSize > $fileSize[0]) {
                $newSize = $fileSize[0] - 1;
            }
            $response = $video->getHttpResponse(['Range' => 'bytes='.$curSize.'-'.$newSize]);
            $this->addStream(new YoutubeChunkStream($response));
            $curSize = $newSize + 1;
        }
    }
}

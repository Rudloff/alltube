<?php
/**
 * ConvertedPlaylistArchiveStream class.
 */

namespace Alltube;

use Slim\Http\Stream;

class ConvertedPlaylistArchiveStream extends PlaylistArchiveStream
{
    /**
     * Start streaming a new video.
     *
     * @param Video $video Video to stream
     *
     * @return void
     */
    protected function startVideoStream(Video $video)
    {
        $this->curVideoStream = new Stream($video->getAudioStream());

        $this->init_file_stream_transfer(
            $video->getFileNameWithExtension('mp3'),
            // The ZIP format does not care about the file size.
            0
        );
    }
}

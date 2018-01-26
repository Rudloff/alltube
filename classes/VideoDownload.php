<?php
/**
 * VideoDownload class.
 */

namespace Alltube;

use Symfony\Component\Process\Process;

/**
 * Extract info about videos.
 */
class VideoDownload
{
    /**
     * Config instance.
     *
     * @var Config
     */
    private $config;

    /**
     * VideoDownload constructor.
     *
     * @param Config $config Config instance.
     *
     * @throws \Exception If youtube-dl is missing
     * @throws \Exception If Python is missing
     */
    public function __construct(Config $config = null)
    {
        if (isset($config)) {
            $this->config = $config;
        } else {
            $this->config = Config::getInstance();
        }
        /*
        We don't translate these exceptions because they always occur before Slim can catch them
        so they will always go to the logs.
         */
        if (!is_file($this->config->youtubedl)) {
            throw new \Exception("Can't find youtube-dl at ".$this->config->youtubedl);
        } elseif (!$this->checkCommand([$this->config->python, '--version'])) {
            throw new \Exception("Can't find Python at ".$this->config->python);
        }
    }

    /**
     * Return a youtube-dl process with the specified arguments.
     *
     * @param string[] $arguments Arguments
     *
     * @return Process
     */
    private function getProcess(array $arguments)
    {
        return new Process(
            array_merge(
                [$this->config->python, $this->config->youtubedl],
                $this->config->params,
                $arguments
            )
        );
    }

    /**
     * List all extractors.
     *
     * @return string[] Extractors
     * */
    public function listExtractors()
    {
        return explode("\n", trim($this->getProp(null, null, 'list-extractors')));
    }

    /**
     * Get a property from youtube-dl.
     *
     * @param string $url      URL to parse
     * @param string $format   Format
     * @param string $prop     Property
     * @param string $password Video password
     *
     * @throws PasswordException If the video is protected by a password and no password was specified
     * @throws \Exception        If the password is wrong
     * @throws \Exception        If youtube-dl returns an error
     *
     * @return string
     */
    private function getProp($url, $format = null, $prop = 'dump-json', $password = null)
    {
        $arguments = [
            '--'.$prop,
            $url,
        ];
        if (isset($format)) {
            $arguments[] = '-f '.$format;
        }
        if (isset($password)) {
            $arguments[] = '--video-password';
            $arguments[] = $password;
        }

        $process = $this->getProcess($arguments);
        //This is needed by the openload extractor because it runs PhantomJS
        $process->setEnv(['PATH'=>$this->config->phantomjsDir]);
        $process->inheritEnvironmentVariables();
        $process->run();
        if (!$process->isSuccessful()) {
            $errorOutput = trim($process->getErrorOutput());
            if ($errorOutput == 'ERROR: This video is protected by a password, use the --video-password option') {
                throw new PasswordException($errorOutput);
            } elseif (substr($errorOutput, 0, 21) == 'ERROR: Wrong password') {
                throw new \Exception(_('Wrong password'));
            } else {
                throw new \Exception($errorOutput);
            }
        } else {
            return trim($process->getOutput());
        }
    }

    /**
     * Get all information about a video.
     *
     * @param string $url      URL of page
     * @param string $format   Format to use for the video
     * @param string $password Video password
     *
     * @return object Decoded JSON
     * */
    public function getJSON($url, $format = null, $password = null)
    {
        return json_decode($this->getProp($url, $format, 'dump-single-json', $password));
    }

    /**
     * Get URL of video from URL of page.
     *
     * It generally returns only one URL.
     * But it can return two URLs when multiple formats are specified
     * (eg. bestvideo+bestaudio).
     *
     * @param string $url      URL of page
     * @param string $format   Format to use for the video
     * @param string $password Video password
     *
     * @return string[] URLs of video
     * */
    public function getURL($url, $format = null, $password = null)
    {
        return explode("\n", $this->getProp($url, $format, 'get-url', $password));
    }

    /**
     * Get filename of video file from URL of page.
     *
     * @param string $url      URL of page
     * @param string $format   Format to use for the video
     * @param string $password Video password
     *
     * @return string Filename of extracted video
     * */
    public function getFilename($url, $format = null, $password = null)
    {
        return trim($this->getProp($url, $format, 'get-filename', $password));
    }

    /**
     * Get filename of video with the specified extension.
     *
     * @param string $extension New file extension
     * @param string $url       URL of page
     * @param string $format    Format to use for the video
     * @param string $password  Video password
     *
     * @return string Filename of extracted video with specified extension
     */
    public function getFileNameWithExtension($extension, $url, $format = null, $password = null)
    {
        return html_entity_decode(
            pathinfo(
                $this->getFilename($url, $format, $password),
                PATHINFO_FILENAME
            ).'.'.$extension,
            ENT_COMPAT,
            'ISO-8859-1'
        );
    }

    /**
     * Get filename of audio from URL of page.
     *
     * @param string $url      URL of page
     * @param string $format   Format to use for the video
     * @param string $password Video password
     *
     * @return string Filename of converted audio file
     * */
    public function getAudioFilename($url, $format = null, $password = null)
    {
        return $this->getFileNameWithExtension('mp3', $url, $format, $password);
    }

    /**
     * Return arguments used to run rtmp for a specific video.
     *
     * @param object $video Video object returned by youtube-dl
     *
     * @return array Arguments
     */
    private function getRtmpArguments(\stdClass $video)
    {
        $arguments = [];

        foreach ([
            'url'           => '-rtmp_tcurl',
            'webpage_url'   => '-rtmp_pageurl',
            'player_url'    => '-rtmp_swfverify',
            'flash_version' => '-rtmp_flashver',
            'play_path'     => '-rtmp_playpath',
            'app'           => '-rtmp_app',
        ] as $property => $option) {
            if (isset($video->{$property})) {
                $arguments[] = $option;
                $arguments[] = $video->{$property};
            }
        }

        if (isset($video->rtmp_conn)) {
            foreach ($video->rtmp_conn as $conn) {
                $arguments[] = '-rtmp_conn';
                $arguments[] = $conn;
            }
        }

        return $arguments;
    }

    /**
     * Check if a command runs successfully.
     *
     * @param array $command Command and arguments
     *
     * @return bool False if the command returns an error, true otherwise
     */
    private function checkCommand(array $command)
    {
        $process = new Process($command);
        $process->run();

        return $process->isSuccessful();
    }

    /**
     * Get a process that runs avconv in order to convert a video.
     *
     * @param object $video        Video object returned by youtube-dl
     * @param int    $audioBitrate Audio bitrate of the converted file
     * @param string $filetype     Filetype of the converted file
     * @param bool   $audioOnly    True to return an audio-only file
     *
     * @throws \Exception If avconv/ffmpeg is missing
     *
     * @return Process Process
     */
    private function getAvconvProcess(\stdClass $video, $audioBitrate, $filetype = 'mp3', $audioOnly = true)
    {
        if (!$this->checkCommand([$this->config->avconv, '-version'])) {
            throw(new \Exception(_('Can\'t find avconv or ffmpeg.')));
        }

        if ($video->protocol == 'rtmp') {
            $rtmpArguments = $this->getRtmpArguments($video);
        } else {
            $rtmpArguments = [];
        }

        if ($audioOnly) {
            $videoArguments = ['-vn'];
        } else {
            $videoArguments = [];
        }

        $arguments = array_merge(
            [
                $this->config->avconv,
                '-v', $this->config->avconvVerbosity,
            ],
            $rtmpArguments,
            [
                '-i', $video->url,
                '-f', $filetype,
                '-b:a', $audioBitrate.'k',
            ],
            $videoArguments,
            [
                'pipe:1',
            ]
        );
        if ($video->url != '-') {
            //Vimeo needs a correct user-agent
            $arguments[] = '-user_agent';
            $arguments[] = $this->getProp(null, null, 'dump-user-agent');
        }

        return new Process($arguments);
    }

    /**
     * Get audio stream of converted video.
     *
     * @param string $url      URL of page
     * @param string $format   Format to use for the video
     * @param string $password Video password
     *
     * @throws \Exception If your try to convert and M3U8 video
     * @throws \Exception If the popen stream was not created correctly
     *
     * @return resource popen stream
     */
    public function getAudioStream($url, $format, $password = null)
    {
        $video = $this->getJSON($url, $format, $password);
        if (in_array($video->protocol, ['m3u8', 'm3u8_native'])) {
            throw(new \Exception(_('Conversion of M3U8 files is not supported.')));
        }

        $avconvProc = $this->getAvconvProcess($video, $this->config->audioBitrate);

        $stream = popen($avconvProc->getCommandLine(), 'r');

        if (!is_resource($stream)) {
            throw new \Exception(_('Could not open popen stream.'));
        }

        return $stream;
    }

    /**
     * Get video stream from an M3U playlist.
     *
     * @param \stdClass $video Video object returned by getJSON
     *
     * @throws \Exception If avconv/ffmpeg is missing
     * @throws \Exception If the popen stream was not created correctly
     *
     * @return resource popen stream
     */
    public function getM3uStream(\stdClass $video)
    {
        if (!$this->checkCommand([$this->config->avconv, '-version'])) {
            throw(new \Exception(_('Can\'t find avconv or ffmpeg.')));
        }

        $process = new Process(
            [
                $this->config->avconv,
                '-v', $this->config->avconvVerbosity,
                '-i', $video->url,
                '-f', $video->ext,
                '-c', 'copy',
                '-bsf:a', 'aac_adtstoasc',
                '-movflags', 'frag_keyframe+empty_moov',
                'pipe:1',
            ]
        );

        $stream = popen($process->getCommandLine(), 'r');
        if (!is_resource($stream)) {
            throw new \Exception(_('Could not open popen stream.'));
        }

        return $stream;
    }

    /**
     * Get an avconv stream to remux audio and video.
     *
     * @param array $urls URLs of the video ($urls[0]) and audio ($urls[1]) files
     *
     * @throws \Exception If the popen stream was not created correctly
     *
     * @return resource popen stream
     */
    public function getRemuxStream(array $urls)
    {
        $process = new Process(
            [
                $this->config->avconv,
                '-v', $this->config->avconvVerbosity,
                '-i', $urls[0],
                '-i', $urls[1],
                '-c', 'copy',
                '-map', '0:v:0 ',
                '-map', '1:a:0',
                '-f', 'matroska',
                'pipe:1',
            ]
        );

        $stream = popen($process->getCommandLine(), 'r');
        if (!is_resource($stream)) {
            throw new \Exception(_('Could not open popen stream.'));
        }

        return $stream;
    }

    /**
     * Get video stream from an RTMP video.
     *
     * @param \stdClass $video Video object returned by getJSON
     *
     * @throws \Exception If the popen stream was not created correctly
     *
     * @return resource popen stream
     */
    public function getRtmpStream(\stdClass $video)
    {
        $process = new Process(
            array_merge(
                [
                    $this->config->avconv,
                    '-v', $this->config->avconvVerbosity,
                ],
                $this->getRtmpArguments($video),
                [
                    '-i', $video->url,
                    '-f', $video->ext,
                    'pipe:1',
                ]
            )
        );
        $stream = popen($process->getCommandLine(), 'r');
        if (!is_resource($stream)) {
            throw new \Exception(_('Could not open popen stream.'));
        }

        return $stream;
    }

    /**
     * Get a Tar stream containing every video in the playlist piped through the server.
     *
     * @param object $video  Video object returned by youtube-dl
     * @param string $format Requested format
     *
     * @throws \Exception If the popen stream was not created correctly
     *
     * @return resource
     */
    public function getPlaylistArchiveStream(\stdClass $video, $format)
    {
        $playlistItems = [];
        foreach ($video->entries as $entry) {
            $playlistItems[] = urlencode($entry->url);
        }
        $stream = fopen('playlist://'.implode(';', $playlistItems).'/'.$format, 'r');
        if (!is_resource($stream)) {
            throw new \Exception(_('Could not open fopen stream.'));
        }

        return $stream;
    }

    /**
     * Get the stream of a converted video.
     *
     * @param string $url          URL of page
     * @param string $format       Source format to use for the conversion
     * @param int    $audioBitrate Audio bitrate of the converted file
     * @param string $filetype     Filetype of the converted file
     * @param string $password     Video password
     *
     * @throws \Exception If your try to convert and M3U8 video
     * @throws \Exception If the popen stream was not created correctly
     *
     * @return resource popen stream
     */
    public function getConvertedStream($url, $format, $audioBitrate, $filetype, $password = null)
    {
        $video = $this->getJSON($url, $format, $password);
        if (in_array($video->protocol, ['m3u8', 'm3u8_native'])) {
            throw(new \Exception(_('Conversion of M3U8 files is not supported.')));
        }

        $avconvProc = $this->getAvconvProcess($video, $audioBitrate, $filetype, false);

        $stream = popen($avconvProc->getCommandLine(), 'r');

        if (!is_resource($stream)) {
            throw new \Exception(_('Could not open popen stream.'));
        }

        return $stream;
    }
}

<?php

/**
 * VideoDownload class.
 */

namespace Alltube;

use Alltube\Exception\EmptyUrlException;
use Alltube\Exception\PasswordException;
use Exception;
use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;
use stdClass;
use Symfony\Component\Process\Process;

/**
 * Extract info about videos.
 *
 * Due to the way youtube-dl behaves, this class can also contain information about a playlist.
 *
 * @property-read string $title         Title
 * @property-read string $protocol      Network protocol (HTTP, RTMP, etc.)
 * @property-read string $url           File URL
 * @property-read string $ext           File extension
 * @property-read string $extractor_key youtube-dl extractor class used
 * @property-read array $entries       List of videos (if the object contains information about a playlist)
 * @property-read array $rtmp_conn
 * @property-read string|null $_type         Object type (usually "playlist" or null)
 * @property-read stdClass $downloader_options
 * @property-read stdClass $http_headers
 */
class Video
{
    /**
     * Config instance.
     *
     * @var Config
     */
    private $config;

    /**
     * URL of the page containing the video.
     *
     * @var string
     */
    private $webpageUrl;

    /**
     * Requested video format.
     *
     * @var string
     */
    private $requestedFormat;

    /**
     * Password.
     *
     * @var string|null
     */
    private $password;

    /**
     * JSON object returned by youtube-dl.
     *
     * @var stdClass
     */
    private $json;

    /**
     * URLs of the video files.
     *
     * @var string[]
     */
    private $urls;

    /**
     * LocaleManager instance.
     *
     * @var LocaleManager
     */
    protected $localeManager;

    /**
     * VideoDownload constructor.
     *
     * @param string $webpageUrl URL of the page containing the video
     * @param string $requestedFormat Requested video format
     *                                (can be any format string accepted by youtube-dl,
     *                                including selectors like "[height<=720]")
     * @param string $password Password
     */
    public function __construct($webpageUrl, $requestedFormat = 'best', $password = null)
    {
        $this->webpageUrl = $webpageUrl;
        $this->requestedFormat = $requestedFormat;
        $this->password = $password;
        $this->config = Config::getInstance();

        $this->localeManager = LocaleManager::getInstance();
    }

    /**
     * Return a youtube-dl process with the specified arguments.
     *
     * @param string[] $arguments Arguments
     *
     * @return Process<string>
     */
    private static function getProcess(array $arguments)
    {
        $config = Config::getInstance();

        return new Process(
            array_merge(
                [$config->python, $config->youtubedl],
                $config->params,
                $arguments
            )
        );
    }

    /**
     * List all extractors.
     *
     * @return string[] Extractors
     *
     * @throws PasswordException
     */
    public static function getExtractors()
    {
        $video = new self('');

        return explode("\n", trim($video->callYoutubedl(['--list-extractors'])));
    }

    /**
     * Call youtube-dl.
     *
     * @param string[] $arguments Arguments
     *
     * @return string Result
     * @throws Exception         If the password is wrong
     * @throws Exception         If youtube-dl returns an error
     *
     * @throws PasswordException If the video is protected by a password and no password was specified
     */
    private function callYoutubedl(array $arguments)
    {
        $config = Config::getInstance();

        $process = self::getProcess($arguments);
        //This is needed by the openload extractor because it runs PhantomJS
        $process->setEnv(['PATH' => $config->phantomjsDir]);
        $process->run();
        if (!$process->isSuccessful()) {
            $errorOutput = trim($process->getErrorOutput());
            $exitCode = intval($process->getExitCode());
            if ($errorOutput == 'ERROR: This video is protected by a password, use the --video-password option') {
                throw new PasswordException($errorOutput, $exitCode);
            } elseif (substr($errorOutput, 0, 21) == 'ERROR: Wrong password') {
                throw new Exception($this->localeManager->t('Wrong password'), $exitCode);
            } else {
                throw new Exception($errorOutput, $exitCode);
            }
        } else {
            return trim($process->getOutput());
        }
    }

    /**
     * Get a property from youtube-dl.
     *
     * @param string $prop Property
     *
     * @return string
     * @throws PasswordException
     */
    private function getProp($prop = 'dump-json')
    {
        $arguments = ['--' . $prop];

        if (isset($this->webpageUrl)) {
            $arguments[] = $this->webpageUrl;
        }
        if (isset($this->requestedFormat)) {
            $arguments[] = '-f';
            $arguments[] = $this->requestedFormat;
        }
        if (isset($this->password)) {
            $arguments[] = '--video-password';
            $arguments[] = $this->password;
        }

        return $this->callYoutubedl($arguments);
    }

    /**
     * Get all information about a video.
     *
     * @return stdClass Decoded JSON
     *
     * @throws PasswordException
     */
    public function getJson()
    {
        if (!isset($this->json)) {
            $this->json = json_decode($this->getProp('dump-single-json'));
        }

        return $this->json;
    }

    /**
     * Magic method to get a property from the JSON object returned by youtube-dl.
     *
     * @param string $name Property
     *
     * @return mixed
     * @throws PasswordException
     */
    public function __get($name)
    {
        if (isset($this->$name)) {
            return $this->getJson()->$name;
        }

        return null;
    }

    /**
     * Magic method to check if the JSON object returned by youtube-dl has a property.
     *
     * @param string $name Property
     *
     * @return bool
     * @throws PasswordException
     */
    public function __isset($name)
    {
        return isset($this->getJson()->$name);
    }

    /**
     * Get URL of video from URL of page.
     *
     * It generally returns only one URL.
     * But it can return two URLs when multiple formats are specified
     * (eg. bestvideo+bestaudio).
     *
     * @return string[] URLs of video
     * @throws EmptyUrlException
     * @throws PasswordException
     */
    public function getUrl()
    {
        // Cache the URLs.
        if (!isset($this->urls)) {
            $this->urls = explode("\n", $this->getProp('get-url'));

            if (empty($this->urls[0])) {
                throw new EmptyUrlException($this->localeManager->t('youtube-dl returned an empty URL.'));
            }
        }

        return $this->urls;
    }

    /**
     * Get filename of video file from URL of page.
     *
     * @return string Filename of extracted video
     *
     * @throws PasswordException
     */
    public function getFilename()
    {
        return trim($this->getProp('get-filename'));
    }

    /**
     * Get filename of video with the specified extension.
     *
     * @param string $extension New file extension
     *
     * @return string Filename of extracted video with specified extension
     * @throws PasswordException
     */
    public function getFileNameWithExtension($extension)
    {
        return str_replace('.' . $this->ext, '.' . $extension, $this->getFilename());
    }

    /**
     * Return arguments used to run rtmp for a specific video.
     *
     * @return string[] Arguments
     */
    private function getRtmpArguments()
    {
        $arguments = [];

        if ($this->protocol == 'rtmp') {
            foreach (
                [
                    'url' => '-rtmp_tcurl',
                    'webpage_url' => '-rtmp_pageurl',
                    'player_url' => '-rtmp_swfverify',
                    'flash_version' => '-rtmp_flashver',
                    'play_path' => '-rtmp_playpath',
                    'app' => '-rtmp_app',
                ] as $property => $option
            ) {
                if (isset($this->{$property})) {
                    $arguments[] = $option;
                    $arguments[] = $this->{$property};
                }
            }

            if (isset($this->rtmp_conn)) {
                foreach ($this->rtmp_conn as $conn) {
                    $arguments[] = '-rtmp_conn';
                    $arguments[] = $conn;
                }
            }
        }

        return $arguments;
    }

    /**
     * Check if a command runs successfully.
     *
     * @param string[] $command Command and arguments
     *
     * @return bool False if the command returns an error, true otherwise
     */
    public static function checkCommand(array $command)
    {
        $process = new Process($command);
        $process->run();

        return $process->isSuccessful();
    }

    /**
     * Get a process that runs avconv in order to convert a video.
     *
     * @param int $audioBitrate Audio bitrate of the converted file
     * @param string $filetype Filetype of the converted file
     * @param bool $audioOnly True to return an audio-only file
     * @param string $from Start the conversion at this time
     * @param string $to End the conversion at this time
     *
     * @return Process<string> Process
     * @throws Exception If avconv/ffmpeg is missing
     *
     */
    private function getAvconvProcess(
        $audioBitrate,
        $filetype = 'mp3',
        $audioOnly = true,
        $from = null,
        $to = null
    ) {
        if (!$this->checkCommand([$this->config->avconv, '-version'])) {
            throw new Exception(
                $this->localeManager->t(
                    "Can't find avconv or ffmpeg at @path.",
                    ['@path' => $this->config->avconv]
                )
            );
        }

        $durationRegex = '/(\d+:)?(\d+:)?(\d+)/';

        $afterArguments = [];

        if ($audioOnly) {
            $afterArguments[] = '-vn';
        }

        if (!empty($from)) {
            if (!preg_match($durationRegex, $from)) {
                throw new Exception($this->localeManager->t('Invalid start time: @from.', ['@from' => $from]));
            }
            $afterArguments[] = '-ss';
            $afterArguments[] = $from;
        }
        if (!empty($to)) {
            if (!preg_match($durationRegex, $to)) {
                throw new Exception($this->localeManager->t('Invalid end time: @to.', ['@to' => $to]));
            }
            $afterArguments[] = '-to';
            $afterArguments[] = $to;
        }

        $urls = $this->getUrl();

        $arguments = array_merge(
            [
                $this->config->avconv,
                '-v', $this->config->avconvVerbosity,
            ],
            $this->getRtmpArguments(),
            [
                '-i', $urls[0],
                '-f', $filetype,
                '-b:a', $audioBitrate . 'k',
            ],
            $afterArguments,
            [
                'pipe:1',
            ]
        );

        //Vimeo needs a correct user-agent
        $arguments[] = '-user_agent';
        $arguments[] = $this->getProp('dump-user-agent');

        return new Process($arguments);
    }

    /**
     * Get audio stream of converted video.
     *
     * @param string $from Start the conversion at this time
     * @param string $to End the conversion at this time
     *
     * @return resource popen stream
     * @throws Exception If the popen stream was not created correctly
     *
     * @throws Exception If your try to convert an M3U8 video
     */
    public function getAudioStream($from = null, $to = null)
    {
        if (isset($this->_type) && $this->_type == 'playlist') {
            throw new Exception($this->localeManager->t('Conversion of playlists is not supported.'));
        }

        if (isset($this->protocol)) {
            if (in_array($this->protocol, ['m3u8', 'm3u8_native'])) {
                throw new Exception($this->localeManager->t('Conversion of M3U8 files is not supported.'));
            } elseif ($this->protocol == 'http_dash_segments') {
                throw new Exception($this->localeManager->t('Conversion of DASH segments is not supported.'));
            }
        }

        $avconvProc = $this->getAvconvProcess($this->config->audioBitrate, 'mp3', true, $from, $to);

        $stream = popen($avconvProc->getCommandLine(), 'r');

        if (!is_resource($stream)) {
            throw new Exception($this->localeManager->t('Could not open popen stream.'));
        }

        return $stream;
    }

    /**
     * Get video stream from an M3U playlist.
     *
     * @return resource popen stream
     * @throws Exception If the popen stream was not created correctly
     *
     * @throws Exception If avconv/ffmpeg is missing
     */
    public function getM3uStream()
    {
        if (!$this->checkCommand([$this->config->avconv, '-version'])) {
            throw new Exception(
                $this->localeManager->t(
                    "Can't find avconv or ffmpeg at @path.",
                    ['@path' => $this->config->avconv]
                )
            );
        }

        $urls = $this->getUrl();

        $process = new Process(
            [
                $this->config->avconv,
                '-v', $this->config->avconvVerbosity,
                '-i', $urls[0],
                '-f', $this->ext,
                '-c', 'copy',
                '-bsf:a', 'aac_adtstoasc',
                '-movflags', 'frag_keyframe+empty_moov',
                'pipe:1',
            ]
        );

        $stream = popen($process->getCommandLine(), 'r');
        if (!is_resource($stream)) {
            throw new Exception($this->localeManager->t('Could not open popen stream.'));
        }

        return $stream;
    }

    /**
     * Get an avconv stream to remux audio and video.
     *
     * @return resource popen stream
     * @throws Exception If the popen stream was not created correctly
     *
     */
    public function getRemuxStream()
    {
        $urls = $this->getUrl();

        if (!isset($urls[0]) || !isset($urls[1])) {
            throw new Exception($this->localeManager->t('This video does not have two URLs.'));
        }

        $process = new Process(
            [
                $this->config->avconv,
                '-v', $this->config->avconvVerbosity,
                '-i', $urls[0],
                '-i', $urls[1],
                '-c', 'copy',
                '-map', '0:v:0',
                '-map', '1:a:0',
                '-f', 'matroska',
                'pipe:1',
            ]
        );

        $stream = popen($process->getCommandLine(), 'r');
        if (!is_resource($stream)) {
            throw new Exception($this->localeManager->t('Could not open popen stream.'));
        }

        return $stream;
    }

    /**
     * Get video stream from an RTMP video.
     *
     * @return resource popen stream
     * @throws Exception If the popen stream was not created correctly
     *
     */
    public function getRtmpStream()
    {
        $urls = $this->getUrl();

        $process = new Process(
            array_merge(
                [
                    $this->config->avconv,
                    '-v', $this->config->avconvVerbosity,
                ],
                $this->getRtmpArguments(),
                [
                    '-i', $urls[0],
                    '-f', $this->ext,
                    'pipe:1',
                ]
            )
        );
        $stream = popen($process->getCommandLine(), 'r');
        if (!is_resource($stream)) {
            throw new Exception($this->localeManager->t('Could not open popen stream.'));
        }

        return $stream;
    }

    /**
     * Get the stream of a converted video.
     *
     * @param int $audioBitrate Audio bitrate of the converted file
     * @param string $filetype Filetype of the converted file
     *
     * @return resource popen stream
     * @throws Exception If the popen stream was not created correctly
     *
     * @throws Exception If your try to convert and M3U8 video
     */
    public function getConvertedStream($audioBitrate, $filetype)
    {
        if (in_array($this->protocol, ['m3u8', 'm3u8_native'])) {
            throw new Exception($this->localeManager->t('Conversion of M3U8 files is not supported.'));
        }

        $avconvProc = $this->getAvconvProcess($audioBitrate, $filetype, false);

        $stream = popen($avconvProc->getCommandLine(), 'r');

        if (!is_resource($stream)) {
            throw new Exception($this->localeManager->t('Could not open popen stream.'));
        }

        return $stream;
    }

    /**
     * Get the same video but with another format.
     *
     * @param string $format New format
     *
     * @return Video
     */
    public function withFormat($format)
    {
        return new self($this->webpageUrl, $format, $this->password);
    }

    /**
     * Get a HTTP response containing the video.
     *
     * @param mixed[] $headers HTTP headers of the request
     *
     * @return ResponseInterface
     * @throws EmptyUrlException
     * @throws PasswordException
     * @link https://github.com/guzzle/guzzle/issues/2640
     */
    public function getHttpResponse(array $headers = [])
    {
        // IDN conversion breaks with Google hosts like https://r3---sn-25glene6.googlevideo.com/.
        $client = new Client(['idn_conversion' => false]);
        $urls = $this->getUrl();
        $stream_context_options = [];

        if (array_key_exists('Referer', (array)$this->http_headers)) {
            $stream_context_options = [
                'http' => [
                    'header' => 'Referer: ' . $this->http_headers->Referer
                ]
            ];
        }

        return $client->request(
            'GET',
            $urls[0],
            [
                'stream' => true,
                'stream_context' => $stream_context_options,
                'headers' => array_merge((array)$this->http_headers, $headers)
            ]
        );
    }
}

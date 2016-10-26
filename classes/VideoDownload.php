<?php
/**
 * VideoDownload class.
 */
namespace Alltube;

use Chain\Chain;
use Symfony\Component\Process\ProcessBuilder;

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
     * ProcessBuilder instance used to call Python.
     *
     * @var ProcessBuilder
     */
    private $procBuilder;

    /**
     * VideoDownload constructor.
     */
    public function __construct(Config $config = null)
    {
        if (isset($config)) {
            $this->config = $config;
        } else {
            $this->config = Config::getInstance();
        }
        $this->procBuilder = new ProcessBuilder();
        if (!is_file($this->config->youtubedl)) {
            throw new \Exception("Can't find youtube-dl at ".$this->config->youtubedl);
        } elseif (!is_file($this->config->python)) {
            throw new \Exception("Can't find Python at ".$this->config->python);
        }
        $this->procBuilder->setPrefix(
            array_merge(
                [$this->config->python, $this->config->youtubedl],
                $this->config->params
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
        return explode(PHP_EOL, trim($this->getProp($url, null, 'list-extractors')));
    }

    /**
     * Get a property from youtube-dl.
     *
     * @param string $url      URL to parse
     * @param string $format   Format
     * @param string $prop     Property
     * @param string $password Video password
     *
     * @return string
     */
    private function getProp($url, $format = null, $prop = 'dump-json', $password = null)
    {
        $this->procBuilder->setArguments(
            [
                '--'.$prop,
                $url,
            ]
        );
        if (isset($format)) {
            $this->procBuilder->add('-f '.$format);
        }
        if (isset($password)) {
            $this->procBuilder->add('--video-password');
            $this->procBuilder->add($password);
        }
        $process = $this->procBuilder->getProcess();
        $process->run();
        if (!$process->isSuccessful()) {
            $errorOutput = trim($process->getErrorOutput());
            if ($errorOutput == 'ERROR: This video is protected by a password, use the --video-password option') {
                throw new PasswordException($errorOutput);
            } elseif (substr($errorOutput, 0, 21) == 'ERROR: Wrong password') {
                throw new \Exception('Wrong password');
            } else {
                throw new \Exception($errorOutput);
            }
        } else {
            return $process->getOutput();
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
        return json_decode($this->getProp($url, $format, 'dump-json', $password));
    }

    /**
     * Get URL of video from URL of page.
     *
     * @param string $url      URL of page
     * @param string $format   Format to use for the video
     * @param string $password Video password
     *
     * @return string URL of video
     * */
    public function getURL($url, $format = null, $password = null)
    {
        return $this->getProp($url, $format, 'get-url', $password);
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
        return html_entity_decode(
            pathinfo(
                $this->getFilename($url, $format, $password),
                PATHINFO_FILENAME
            ).'.mp3',
            ENT_COMPAT,
            'ISO-8859-1'
        );
    }

    /**
     * Add options to a process builder running rtmp.
     *
     * @param ProcessBuilder $builder Process builder
     * @param object         $video   Video object returned by youtube-dl
     *
     * @return ProcessBuilder
     */
    private function addOptionsToRtmpProcess(ProcessBuilder $builder, $video)
    {
        foreach ([
            'url'           => 'rtmp',
            'webpage_url'   => 'pageUrl',
            'player_url'    => 'swfVfy',
            'flash_version' => 'flashVer',
            'play_path'     => 'playpath',
            'app'           => 'app',
        ] as $property => $option) {
            if (isset($video->{$property})) {
                $builder->add('--'.$option);
                $builder->add($video->{$property});
            }
        }

        return $builder;
    }

    /**
     * Get a process that runs rtmp in order to download a video.
     *
     * @param object $video Video object returned by youtube-dl
     *
     * @return \Symfony\Component\Process\Process Process
     */
    private function getRtmpProcess($video)
    {
        if (!shell_exec('which '.$this->config->rtmpdump)) {
            throw(new \Exception('Can\'t find rtmpdump'));
        }
        $builder = new ProcessBuilder(
            [
                $this->config->rtmpdump,
                '-q',
            ]
        );
        $builder = $this->addOptionsToRtmpProcess($builder, $video);
        if (isset($video->rtmp_conn)) {
            foreach ($video->rtmp_conn as $conn) {
                $builder->add('--conn');
                $builder->add($conn);
            }
        }

        return $builder->getProcess();
    }

    /**
     * Get a process that runs curl in order to download a video.
     *
     * @param object $video Video object returned by youtube-dl
     *
     * @return \Symfony\Component\Process\Process Process
     */
    private function getCurlProcess($video)
    {
        if (!shell_exec('which '.$this->config->curl)) {
            throw(new \Exception('Can\'t find curl'));
        }
        $builder = ProcessBuilder::create(
            array_merge(
                [
                    $this->config->curl,
                    '--silent',
                    '--location',
                    '--user-agent', $video->http_headers->{'User-Agent'},
                    $video->url,
                ],
                $this->config->curl_params
            )
        );

        return $builder->getProcess();
    }

    /**
     * Get audio stream of converted video.
     *
     * @param string $url      URL of page
     * @param string $format   Format to use for the video
     * @param string $password Video password
     *
     * @return resource popen stream
     */
    public function getAudioStream($url, $format, $password = null)
    {
        if (!shell_exec('which '.$this->config->avconv)) {
            throw(new \Exception('Can\'t find avconv or ffmpeg'));
        }

        $video = $this->getJSON($url, $format, $password);

        //Vimeo needs a correct user-agent
        ini_set(
            'user_agent',
            $video->http_headers->{'User-Agent'}
        );
        $avconvProc = ProcessBuilder::create(
            [
                $this->config->avconv,
                '-v', 'quiet',
                '-i', '-',
                '-f', 'mp3',
                '-vn',
                'pipe:1',
            ]
        );

        if (parse_url($video->url, PHP_URL_SCHEME) == 'rtmp') {
            $process = $this->getRtmpProcess($video);
        } else {
            $process = $this->getCurlProcess($video);
        }
        $chain = new Chain($process);
        $chain->add('|', $avconvProc);

        return popen($chain->getProcess()->getCommandLine(), 'r');
    }
}

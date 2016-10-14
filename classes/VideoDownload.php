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
    public function __construct()
    {
        $this->config = Config::getInstance();
        $this->procBuilder = new ProcessBuilder();
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
        $this->procBuilder->setArguments(
            [
                '--list-extractors',
            ]
        );
        $process = $this->procBuilder->getProcess();
        $process->run();

        return explode(PHP_EOL, trim($process->getOutput()));
    }

    /**
     * Get a property from youtube-dl.
     *
     * @param string $url    URL to parse
     * @param string $format Format
     * @param string $prop   Property
     *
     * @return string
     */
    private function getProp($url, $format = null, $prop = 'dump-json')
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
        $process = $this->procBuilder->getProcess();
        $process->run();
        if (!$process->isSuccessful()) {
            throw new \Exception($process->getErrorOutput());
        } else {
            return $process->getOutput();
        }
    }

    /**
     * Get all information about a video.
     *
     * @param string $url    URL of page
     * @param string $format Format to use for the video
     *
     * @return object Decoded JSON
     * */
    public function getJSON($url, $format = null)
    {
        return json_decode($this->getProp($url, $format, 'dump-json'));
    }

    /**
     * Get URL of video from URL of page.
     *
     * @param string $url    URL of page
     * @param string $format Format to use for the video
     *
     * @return string URL of video
     * */
    public function getURL($url, $format = null)
    {
        return $this->getProp($url, $format, 'get-url');
    }

    /**
     * Get filename of video file from URL of page.
     *
     * @param string $url    URL of page
     * @param string $format Format to use for the video
     *
     * @return string Filename of extracted video
     * */
    public function getFilename($url, $format = null)
    {
        return trim($this->getProp($url, $format, 'get-filename'));
    }

    /**
     * Get filename of audio from URL of page.
     *
     * @param string $url    URL of page
     * @param string $format Format to use for the video
     *
     * @return string Filename of converted audio file
     * */
    public function getAudioFilename($url, $format = null)
    {
        return html_entity_decode(
            pathinfo(
                $this->getFilename($url, $format),
                PATHINFO_FILENAME
            ).'.mp3',
            ENT_COMPAT,
            'ISO-8859-1'
        );
    }

    /**
     * Get audio stream of converted video.
     *
     * @param string $url    URL of page
     * @param string $format Format to use for the video
     *
     * @return resource popen stream
     */
    public function getAudioStream($url, $format)
    {
        if (!shell_exec('which '.$this->config->avconv)) {
            throw(new \Exception('Can\'t find avconv or ffmpeg'));
        }

        $video = $this->getJSON($url, $format);

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
            if (!shell_exec('which '.$this->config->rtmpdump)) {
                throw(new \Exception('Can\'t find rtmpdump'));
            }
            $builder = new ProcessBuilder(
                [
                    $this->config->rtmpdump,
                    '-q',
                    '-r',
                    $video->url,
                    '--pageUrl', $video->webpage_url,
                ]
            );
            if (isset($video->player_url)) {
                $builder->add('--swfVfy');
                $builder->add($video->player_url);
            }
            if (isset($video->flash_version)) {
                $builder->add('--flashVer');
                $builder->add($video->flash_version);
            }
            if (isset($video->play_path)) {
                $builder->add('--playpath');
                $builder->add($video->play_path);
            }
            if (isset($video->rtmp_conn)) {
                foreach ($video->rtmp_conn as $conn) {
                    $builder->add('--conn');
                    $builder->add($conn);
                }
            }
            if (isset($video->app)) {
                $builder->add('--app');
                $builder->add($video->app);
            }
            $chain = new Chain($builder->getProcess());
            $chain->add('|', $avconvProc);
        } else {
            if (!shell_exec('which '.$this->config->curl)) {
                throw(new \Exception('Can\'t find curl'));
            }
            $chain = new Chain(
                ProcessBuilder::create(
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
                )
            );
            $chain->add('|', $avconvProc);
        }

        return popen($chain->getProcess()->getCommandLine(), 'r');
    }
}

<?php
/**
 * Config class.
 */

namespace Alltube;

use Symfony\Component\Yaml\Yaml;

/**
 * Manage config parameters.
 */
class Config
{
    /**
     * Singleton instance.
     *
     * @var Config
     */
    private static $instance;

    /**
     * youtube-dl binary path.
     *
     * @var string
     */
    public $youtubedl = 'vendor/rg3/youtube-dl/youtube_dl/__main__.py';

    /**
     * python binary path.
     *
     * @var string
     */
    public $python = '/usr/bin/python';

    /**
     * youtube-dl parameters.
     *
     * @var array
     */
    public $params = ['--no-warnings', '--ignore-errors', '--flat-playlist', '--restrict-filenames'];

    /**
     * Enable audio conversion.
     *
     * @var bool
     */
    public $convert = false;

    /**
     * avconv or ffmpeg binary path.
     *
     * @var string
     */
    public $avconv = 'vendor/bin/ffmpeg';

    /**
     * Disable URL rewriting.
     *
     * @var bool
     */
    public $uglyUrls = false;

    /**
     * Stream downloaded files trough server?
     *
     * @var bool
     */
    public $stream = false;

    /**
     * Allow to remux video + audio?
     *
     * @var bool
     */
    public $remux = false;

    /**
     * MP3 bitrate when converting (in kbit/s).
     *
     * @var int
     */
    public $audioBitrate = 128;

    /**
     * avconv/ffmpeg logging level.
     * Must be one of these: quiet, panic, fatal, error, warning, info, verbose, debug.
     *
     * @var string
     */
    public $avconvVerbosity = 'error';

    /**
     * YAML config file path.
     *
     * @var string
     */
    private $file;

    /**
     * Config constructor.
     *
     * Available options:
     * * youtubedl: youtube-dl binary path
     * * python: Python binary path
     * * avconv: avconv or ffmpeg binary path
     * * params: Array of youtube-dl parameters
     * * convert: Enable conversion?
     *
     * @param array $options Options
     */
    public function __construct(array $options)
    {
        if (isset($options) && is_array($options)) {
            foreach ($options as $option => $value) {
                if (isset($this->$option) && isset($value)) {
                    $this->$option = $value;
                }
            }
        }
        if (getenv('CONVERT')) {
            $this->convert = (bool) getenv('CONVERT');
        }
        if (getenv('PYTHON')) {
            $this->python = getenv('PYTHON');
        }
    }

    /**
     * Get Config singleton instance from YAML config file.
     *
     * @param string $yamlfile YAML config file name
     *
     * @return Config
     */
    public static function getInstance($yamlfile = 'config/config.yml')
    {
        $yamlPath = __DIR__.'/../'.$yamlfile;
        if (is_null(self::$instance) || self::$instance->file != $yamlfile) {
            if (is_file($yamlfile)) {
                $options = Yaml::parse(file_get_contents($yamlPath));
            } elseif ($yamlfile == 'config/config.yml' || empty($yamlfile)) {
                /*
                Allow for the default file to be missing in order to
                not surprise users that did not create a config file
                 */
                $options = [];
            } else {
                throw new \Exception("Can't find config file at ".$yamlPath);
            }
            self::$instance = new self($options);
            self::$instance->file = $yamlfile;
        }

        return self::$instance;
    }

    /**
     * Destroy singleton instance.
     *
     * @return void
     */
    public static function destroyInstance()
    {
        self::$instance = null;
    }
}

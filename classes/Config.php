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
     * Enable advanced conversion mode.
     *
     * @var bool
     */
    public $convertAdvanced = false;

    /**
     * List of formats available in advanced conversion mode.
     *
     * @var array
     */
    public $convertAdvancedFormats = ['mp3', 'avi', 'flv', 'wav'];

    /**
     * avconv or ffmpeg binary path.
     *
     * @var string
     */
    public $avconv = 'vendor/bin/ffmpeg';

    /**
     * Path to the directory that contains the phantomjs binary.
     *
     * @var string
     */
    public $phantomjsDir = 'vendor/bin/';

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
     * @param array $options Options (see `config/config.example.yml` for available options)
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
        $this->getEnv();
    }

    /**
     * Override options from environement variables.
     * Supported environment variables: CONVERT, PYTHON, AUDIO_BITRATE.
     *
     * @return void
     */
    private function getEnv()
    {
        foreach (['CONVERT', 'PYTHON', 'AUDIO_BITRATE'] as $var) {
            $env = getenv($var);
            if ($env) {
                $prop = lcfirst(str_replace('_', '', ucwords(strtolower($var), '_')));
                $this->$prop = $env;
            }
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

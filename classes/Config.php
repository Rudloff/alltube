<?php

/**
 * Config class.
 */

namespace Alltube;

use Alltube\Exception\ConfigException;
use Alltube\Library\Downloader;
use Jawira\CaseConverter\CaseConverterException;
use Jean85\PrettyVersions;
use PackageVersions\Versions;
use Symfony\Component\ErrorHandler\Debug;
use Symfony\Component\Yaml\Yaml;
use Jawira\CaseConverter\Convert;

/**
 * Manage config parameters.
 */
class Config
{
    /**
     * Singleton instance.
     *
     * @var Config|null
     */
    private static $instance;

    /**
     * youtube-dl binary path.
     *
     * @var string
     */
    public $youtubedl = 'vendor/ytdl-org/youtube-dl/youtube_dl/__main__.py';

    /**
     * python binary path.
     *
     * @var string
     */
    public $python = '/usr/bin/python';

    /**
     * youtube-dl parameters.
     *
     * @var string[]
     */
    public $params = ['--no-warnings', '--ignore-errors', '--flat-playlist', '--restrict-filenames', '--no-playlist'];

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
     * @var string[]
     */
    public $convertAdvancedFormats = ['mp3', 'avi', 'flv', 'wav'];

    /**
     * ffmpeg binary path.
     *
     * @var string
     */
    public $ffmpeg = '/usr/bin/ffmpeg';

    /**
     * Path to the directory that contains the phantomjs binary.
     *
     * @var string
     */
    public $phantomjsDir = '/usr/bin/';

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
     * ffmpeg logging level.
     * Must be one of these: quiet, panic, fatal, error, warning, info, verbose, debug.
     *
     * @var string
     */
    public $ffmpegVerbosity = 'error';

    /**
     * App name.
     *
     * @var string
     */
    public $appName = 'AllTube Download';

    /**
     * Generic formats supported by youtube-dl.
     *
     * @var string[]
     */
    public $genericFormats = [];

    /**
     * Enable debug mode.
     *
     * @var bool
     */
    public $debug = false;

    /**
     * Default to audio.
     *
     * @var bool
     */
    public $defaultAudio = false;

    /**
     * Disable audio conversion from/to seeker.
     *
     * @var bool
     */
    public $convertSeek = true;

    /**
     * Config constructor.
     *
     * @param mixed[] $options Options
     * @throws ConfigException
     */
    private function __construct(array $options = [])
    {
        $this->applyOptions($options);
        $this->getEnv();
        $localeManager = LocaleManager::getInstance();

        if (empty($this->genericFormats)) {
            // We don't put this in the class definition so it can be detected by xgettext.
            $this->genericFormats = [
                'best/bestvideo' => $localeManager->t('Best'),
                'bestvideo+bestaudio' => $localeManager->t('Remux best video with best audio'),
                'worst/worstvideo' => $localeManager->t('Worst'),
            ];
        }

        foreach ($this->genericFormats as $format => $name) {
            if (strpos($format, '+') !== false) {
                if (!$this->remux) {
                    // Disable combined formats if remux mode is not enabled.
                    unset($this->genericFormats[$format]);
                }
            } elseif (!$this->stream) {
                // Force HTTP if stream is not enabled.
                $keys = array_keys($this->genericFormats);
                $keys[array_search($format, $keys)] = $this->addHttpToFormat($format);
                if ($genericFormats = array_combine($keys, $this->genericFormats)) {
                    $this->genericFormats = $genericFormats;
                }
            }
        }
    }

    /**
     * Add HTTP condition to a format.
     *
     * @param string $format Format
     *
     * @return string
     */
    public static function addHttpToFormat(string $format)
    {
        $newFormat = [];
        foreach (explode('/', $format) as $subformat) {
            $newFormat[] = $subformat . '[protocol=https]';
            $newFormat[] = $subformat . '[protocol=http]';
        }

        return implode('/', $newFormat);
    }

    /**
     * Throw an exception if some of the options are invalid.
     *
     * @return void
     * @throws ConfigException If Python is missing
     * @throws ConfigException If youtube-dl is missing
     */
    private function validateOptions()
    {
        if (!is_file($this->youtubedl)) {
            throw new ConfigException("Can't find youtube-dl at " . $this->youtubedl);
        } elseif (!Downloader::checkCommand([$this->python, '--version'])) {
            throw new ConfigException("Can't find Python at " . $this->python);
        }

        if (!class_exists(Debug::class)) {
            // Dev dependencies are probably not installed.
            $this->debug = false;
        }
    }

    /**
     * Apply the provided options.
     *
     * @param mixed[] $options Options
     *
     * @return void
     */
    private function applyOptions(array $options)
    {
        foreach ($options as $option => $value) {
            if (isset($this->$option) && isset($value)) {
                $this->$option = $value;
            }
        }
    }

    /**
     * Override options from environment variables.
     * Environment variables should use screaming snake case: CONVERT, PYTHON, AUDIO_BITRATE, etc.
     * If the value is an array, you should use the YAML format: "CONVERT_ADVANCED_FORMATS='[foo, bar]'"
     *
     * @return void
     * @throws ConfigException
     */
    private function getEnv()
    {
        foreach (get_object_vars($this) as $prop => $value) {
            try {
                $convert = new Convert($prop);
                $env = getenv($convert->toMacro());
            } catch (CaseConverterException $e) {
                // This should not happen.
                throw new ConfigException('Could not parse option name: ' . $prop, $e->getCode(), $e);
            }
            if ($env) {
                $this->$prop = Yaml::parse($env);
            }
        }
    }

    /**
     * Get Config singleton instance.
     *
     * @return Config
     * @todo Stop using a singleton.
     */
    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Set options from a YAML file.
     *
     * @param string $file Path to the YAML file
     * @return void
     * @throws ConfigException
     */
    public static function setFile(string $file)
    {
        if (is_file($file)) {
            $options = Yaml::parse(strval(file_get_contents($file)));
            self::$instance = new self($options);
            self::$instance->validateOptions();
        } else {
            throw new ConfigException("Can't find config file at " . $file);
        }
    }

    /**
     * Manually set some options.
     *
     * @param mixed[] $options Options (see `config/config.example.yml` for available options)
     * @param bool $update True to update an existing instance
     * @return void
     * @throws ConfigException
     */
    public static function setOptions(array $options, $update = true)
    {
        if ($update) {
            $config = self::getInstance();
            $config->applyOptions($options);
            $config->validateOptions();
        } else {
            self::$instance = new self($options);
        }
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

    /**
     * Return a downloader object with the current config.
     *
     * @return Downloader
     */
    public function getDownloader()
    {
        return new Downloader(
            $this->youtubedl,
            $this->params,
            $this->python,
            $this->ffmpeg,
            $this->phantomjsDir,
            $this->ffmpegVerbosity
        );
    }

    /**
     * @return string
     */
    public function getAppVersion()
    {
        $version = PrettyVersions::getVersion(Versions::ROOT_PACKAGE_NAME);

        return $version->getPrettyVersion();
    }
}

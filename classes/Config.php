<?php

/**
 * Config class.
 */

namespace Alltube;

use Alltube\Exception\ConfigException;
use Alltube\Library\Downloader;
use Jawira\CaseConverter\CaseConverterException;
use Jean85\PrettyVersions;
use Symfony\Component\ErrorHandler\Debug;
use Symfony\Component\Yaml\Yaml;
use Jawira\CaseConverter\Convert;

/**
 * Manage config parameters.
 */
class Config
{
    /**
     * youtube-dl binary path.
     *
     * @var string
     */
    public string $youtubedl = 'vendor/yt-dlp/yt-dlp/yt_dlp/__main__.py';

    /**
     * python binary path.
     *
     * @var string
     */
    public string $python = '/usr/bin/python';

    /**
     * youtube-dl parameters.
     *
     * @var string[]
     */
    public array $params = [
        '--no-warnings',
        '--ignore-errors',
        '--flat-playlist',
        '--restrict-filenames',
        '--no-playlist',
        '--use-extractors',
        'default,-generic',
    ];

    /**
     * Enable audio conversion.
     *
     * @var bool
     */
    public bool $convert = false;

    /**
     * Enable advanced conversion mode.
     *
     * @var bool
     */
    public bool $convertAdvanced = false;

    /**
     * List of formats available in advanced conversion mode.
     *
     * @var string[]
     */
    public array $convertAdvancedFormats = ['mp3', 'avi', 'flv', 'wav'];

    /**
     * ffmpeg binary path.
     *
     * @var string
     */
    public string $ffmpeg = '/usr/bin/ffmpeg';

    /**
     * Path to the directory that contains the phantomjs binary.
     *
     * @var string
     */
    public string $phantomjsDir = '/usr/bin/';

    /**
     * Disable URL rewriting.
     *
     * @var bool
     */
    public bool $uglyUrls = false;

    /**
     * Stream downloaded files trough server?
     *
     * @var bool
     */
    public bool $stream = false;

    /**
     * Allow to remux video + audio?
     *
     * @var bool
     */
    public bool $remux = false;

    /**
     * MP3 bitrate when converting (in kbit/s).
     *
     * @var int
     */
    public int $audioBitrate = 128;

    /**
     * ffmpeg logging level.
     * Must be one of these: quiet, panic, fatal, error, warning, info, verbose, debug.
     *
     * @var string
     */
    public string $ffmpegVerbosity = 'error';

    /**
     * App name.
     *
     * @var string
     */
    public string $appName = 'AllTube Download';

    /**
     * Generic formats supported by youtube-dl.
     *
     * @var string[]
     */
    public array $genericFormats = [
        'best/bestvideo' => 'Best',
        'bestvideo+bestaudio' => 'Remux best video with best audio',
        'worst/worstvideo' => 'Worst',
    ];

    /**
     * Enable debug mode.
     *
     * @var bool
     */
    public bool $debug = false;

    /**
     * Default to audio.
     *
     * @var bool
     */
    public bool $defaultAudio = false;

    /**
     * Disable audio conversion from/to seeker.
     *
     * @var bool
     */
    public bool $convertSeek = true;

    /**
     * Config constructor.
     *
     * @param scalar[]|scalar[][]|null[] $options Options
     * @throws ConfigException
     */
    public function __construct(array $options = [])
    {
        $this->applyOptions($options);
        $this->getEnv();
        $this->validateOptions();

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
    public static function addHttpToFormat(string $format): string
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
    private function validateOptions(): void
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
     * @param scalar[]|scalar[][]|null[] $options Options
     *
     * @return void
     */
    private function applyOptions(array $options): void
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
    private function getEnv(): void
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
     * Set options from a YAML file.
     *
     * @param string $file Path to the YAML file
     * @return Config
     * @throws ConfigException
     */
    public static function fromFile(string $file): Config
    {
        if (is_file($file)) {
            return new self(Yaml::parse(strval(file_get_contents($file))));
        } else {
            throw new ConfigException("Can't find config file at " . $file);
        }
    }

    /**
     * Manually set some options.
     *
     * @param scalar[]|scalar[][]|null[] $options Options (see `config/config.example.yml` for available options)
     * @return void
     * @throws ConfigException
     */
    public function setOptions(array $options): void
    {
        $this->applyOptions($options);
        $this->validateOptions();
    }

    /**
     * Return a downloader object with the current config.
     *
     * @return Downloader
     */
    public function getDownloader(): Downloader
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
    public function getAppVersion(): string
    {
        $version = PrettyVersions::getRootPackageVersion();

        return $version->getPrettyVersion();
    }
}

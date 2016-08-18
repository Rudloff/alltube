<?php
/**
 * Config class
 */
namespace Alltube;

use Symfony\Component\Yaml\Yaml;

/**
 * Manage config parameters
 */
class Config
{
    /**
     * Singleton instance
     * @var Config
     */
    private static $instance;

    /**
     * youtube-dl binary path
     * @var string
     */
    public $youtubedl = 'vendor/rg3/youtube-dl/youtube_dl/__main__.py';

    /**
     * python binary path
     * @var string
     */
    public $python = '/usr/bin/python';

    /**
     * youtube-dl parameters
     * @var array
     */
    public $params = array('--no-playlist', '--no-warnings', '-f best[protocol^=http]', '--playlist-end', 1);

    /**
     * Enable audio conversion
     * @var bool
     */
    public $convert = false;

    /**
     * avconv or ffmpeg binary path
     * @var string
     */
    public $avconv = 'vendor/bin/ffmpeg';

    /**
     * rtmpdump binary path
     * @var string
     */
    public $rtmpdump = 'vendor/bin/rtmpdump';

    /**
     * curl binary path
     * @var string
     */
    public $curl = '/usr/bin/curl';

    /**
     * curl parameters
     * @var array
     */
    public $curl_params = array();

    private $configFile;

    /**
     * Config constructor
     */
    private function __construct($yamlfile)
    {
        $this->file = $yamlfile;
        if (is_file($yamlfile)) {
            $yaml = Yaml::parse(file_get_contents($yamlfile));
            if (isset($yaml) && is_array($yaml)) {
                foreach ($yaml as $param => $value) {
                    if (isset($this->$param) && isset($value)) {
                        $this->$param = $value;
                    }
                }
            }
        }
        if (getenv('CONVERT')) {
            $this->convert = getenv('CONVERT');
        }
    }

    /**
     * Get singleton instance
     *
     * @return Config
     */
    public static function getInstance($yamlfile = 'config.yml')
    {
        $yamlfile = __DIR__.'/../'.$yamlfile;
        if (is_null(self::$instance) || self::$instance->file != $yamlfile) {
            self::$instance = new Config($yamlfile);
        }
        return self::$instance;
    }

    /**
     * Destroy singleton instance
     * @return void
     */
    public static function destroyInstance()
    {
        self::$instance = null;
    }
}

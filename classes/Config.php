<?php
/**
 * Config class
 *
 * PHP Version 5.3.10
 *
 * @category Youtube-dl
 * @package  Youtubedl
 * @author   Pierre Rudloff <contact@rudloff.pro>
 * @license  GNU General Public License http://www.gnu.org/licenses/gpl.html
 * @link     http://rudloff.pro
 * */
namespace Alltube;

use Symfony\Component\Yaml\Yaml;

/**
 * Class to manage config parameters
 *
 * PHP Version 5.3.10
 *
 * @category Youtube-dl
 * @package  Youtubedl
 * @author   Pierre Rudloff <contact@rudloff.pro>
 * @license  GNU General Public License http://www.gnu.org/licenses/gpl.html
 * @link     http://rudloff.pro
 * */
class Config
{
    private static $instance;

    public $youtubedl = 'vendor/rg3/youtube-dl/youtube_dl/__main__.py';
    public $python = '/usr/bin/python';
    public $params = array('--no-playlist', '--no-warnings', '-f best[protocol^=http]', '--playlist-end', 1);
    public $convert = false;
    public $avconv = 'vendor/bin/ffmpeg';
    public $rtmpdump = 'vendor/bin/rtmpdump';
    public $curl_params = array();

    /**
     * Config constructor
     */
    private function __construct()
    {
        $yamlfile = __DIR__.'/../config.yml';
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
    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new Config();
        }
        return self::$instance;
    }
}

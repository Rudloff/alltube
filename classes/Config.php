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
Class Config
{
    private static $_instance;

    public $youtubedl = __DIR__.'/../vendor/rg3/youtube-dl/youtube_dl/__main__.py';
    public $python = '/usr/bin/python';
    public $params = '--no-playlist --no-warnings -f best';
    public $convert = false;
    public $avconv = __DIR__.'/ffmpeg/ffmpeg';

    private function __construct() {
        $yaml = Yaml::parse(__DIR__.'/../config.yml');
        foreach ($yaml as $param=>$value) {
            if (isset($this->$param)) {
                $this->$param = $value;
            }
        }
        if (getenv('CONVERT')) {
            $this->convert = getenv('CONVERT');
        }
    }

    public static function getInstance() {
        if(is_null(self::$_instance)) {
            self::$_instance = new Config();
        }
        return self::$_instance;
    }
}

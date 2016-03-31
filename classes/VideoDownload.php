<?php
/**
 * VideoDownload class
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

use Symfony\Component\Process\Process;

/**
 * Main class
 *
 * PHP Version 5.3.10
 *
 * @category Youtube-dl
 * @package  Youtubedl
 * @author   Pierre Rudloff <contact@rudloff.pro>
 * @license  GNU General Public License http://www.gnu.org/licenses/gpl.html
 * @link     http://rudloff.pro
 * */
class VideoDownload
{
    /**
     * Get the user agent used youtube-dl
     *
     * @return string UA
     * */
    public static function getUA()
    {
        $config = Config::getInstance();
        $cmd = escapeshellcmd(
            $config->python.' '.escapeshellarg($config->youtubedl).
                ' '.$config->params
        );
        $process = new Process($cmd.' --dump-user-agent');
        $process->run();
        return trim($process->getOutput());
    }

    /**
     * List all extractors
     *
     * @return array Extractors
     * */
    public static function listExtractors()
    {
        $config = Config::getInstance();
        $cmd = escapeshellcmd(
            $config->python.' '.escapeshellarg($config->youtubedl).
                ' '.$config->params
        );
        $process = new Process($cmd.' --list-extractors');
        $process->run();
        return explode(PHP_EOL, $process->getOutput());
    }

    /**
     * Get filename of video
     *
     * @param string $url    URL of page
     * @param string $format Format to use for the video
     *
     * @return string Filename
     * */
    public static function getFilename($url, $format = null)
    {
        $config = Config::getInstance();
        $cmd = escapeshellcmd(
            $config->python.' '.escapeshellarg($config->youtubedl).
                ' '.$config->params
        );
        if (isset($format)) {
            $cmd .= ' -f '.escapeshellarg($format);
        }
        $cmd .=' --get-filename '.escapeshellarg($url)." 2>&1";
        $process = new Process($cmd);
        $process->run();
        return trim($process->getOutput());
    }

    /**
     * Get all information about a video
     *
     * @param string $url    URL of page
     * @param string $format Format to use for the video
     *
     * @return string JSON
     * */
    public static function getJSON($url, $format = null)
    {
        $config = Config::getInstance();
        $cmd = escapeshellcmd(
            $config->python.' '.escapeshellarg($config->youtubedl).
                ' '.$config->params
        );
        if (isset($format)) {
            $cmd .= ' -f '.escapeshellarg($format);
        }
        $cmd .=' --dump-json '.escapeshellarg($url)." 2>&1";
        $process = new Process($cmd);
        $process->run();
        if (!$process->isSuccessful()) {
            throw new \Exception($process->getOutput());
        } else {
            return json_decode($process->getOutput());
        }
    }

    /**
     * Get URL of video from URL of page
     *
     * @param string $url    URL of page
     * @param string $format Format to use for the video
     *
     * @return string URL of video
     * */
    public static function getURL($url, $format = null)
    {
        $config = Config::getInstance();
        $cmd = escapeshellcmd(
            $config->python.' '.escapeshellarg($config->youtubedl).
                ' '.$config->params
        );
        if (isset($format)) {
            $cmd .= ' -f '.escapeshellarg($format);
        }
        $cmd .=' -g '.escapeshellarg($url)." 2>&1";
        $process = new Process($cmd);
        $process->run();
        if (!$process->isSuccessful()) {
            throw new \Exception($process->getOutput());
        } else {
            return array('success'=>true, 'url'=>$process->getOutput());
        }

    }
}

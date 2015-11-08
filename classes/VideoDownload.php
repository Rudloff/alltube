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
Class VideoDownload
{
    /**
     * Get the user agent used youtube-dl
     *
     * @return string UA
     * */
    static function getUA()
    {
        $config = Config::getInstance();
        exec(
            $config->python.' '.$config->youtubedl.' --dump-user-agent',
            $version
        );
        return $version[0];
    }

    /**
     * List all extractors
     *
     * @return array Extractors
     * */
    static function listExtractors()
    {
        $config = Config::getInstance();
        exec(
            $config->python.' '.$config->youtubedl.' --list-extractors',
            $extractors
        );
        return $extractors;
    }

    /**
     * Get filename of video
     *
     * @param string $url    URL of page
     * @param string $format Format to use for the video
     *
     * @return string Filename
     * */
    static function getFilename($url, $format=null)
    {
        $config = Config::getInstance();
        $cmd=$config->python.' '.$config->youtubedl;
        if (isset($format)) {
            $cmd .= ' -f '.escapeshellarg($format);
        }
        $cmd .=' --get-filename '.escapeshellarg($url)." 2>&1";
        exec(
            $cmd,
            $filename
        );
        return end($filename);
    }

    /**
     * Get all information about a video
     *
     * @param string $url    URL of page
     * @param string $format Format to use for the video
     *
     * @return string JSON
     * */
    static function getJSON($url, $format=null)
    {
        $config = Config::getInstance();
        $cmd=$config->python.' '.$config->youtubedl.' '.$config->params;
        if (isset($format)) {
            $cmd .= ' -f '.escapeshellarg($format);
        }
        $cmd .=' --dump-json '.escapeshellarg($url)." 2>&1";
        exec(
            $cmd, $result, $code
        );
        if ($code>0) {
            throw new \Exception(implode(PHP_EOL, $result));
        } else {
            return json_decode($result[0]);
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
    static function getURL($url, $format=null)
    {
        $config = Config::getInstance();
        $cmd=$config->python.' '.$config->youtubedl.' '.$config->params;
        if (isset($format)) {
            $cmd .= ' -f '.escapeshellarg($format);
        }
        $cmd .=' -g '.escapeshellarg($url)." 2>&1";
        exec(
            $cmd, $result, $code
        );
        if ($code>0) {
            throw new \Exception(implode(PHP_EOL, $result));
        } else {
            return array('success'=>true, 'url'=>end($result));
        }

    }
}

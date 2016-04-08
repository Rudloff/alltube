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
use Symfony\Component\Process\ProcessBuilder;

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
    public function __construct()
    {
        $this->config = Config::getInstance();
        $this->procBuilder = new ProcessBuilder();
        $this->procBuilder->setPrefix(
            array_merge(
                array($this->config->python, $this->config->youtubedl),
                $this->config->params
            )
        );
    }

    /**
     * Get the user agent used youtube-dl
     *
     * @return string UA
     * */
    public function getUA()
    {
        $this->procBuilder->setArguments(
            array(
                '--dump-user-agent'
            )
        );
        $process = $this->procBuilder->getProcess();
        $process->run();
        return trim($process->getOutput());
    }

    /**
     * List all extractors
     *
     * @return array Extractors
     * */
    public function listExtractors()
    {
        $this->procBuilder->setArguments(
            array(
                '--list-extractors'
            )
        );
        $process = $this->procBuilder->getProcess();
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
    public function getFilename($url, $format = null)
    {
        $this->procBuilder->setArguments(
            array(
                '--get-filename',
                $url
            )
        );
        if (isset($format)) {
            $this->procBuilder->add('-f '.$format);
        }
        $process = $this->procBuilder->getProcess();
        $process->run();
        return trim($process->getOutput());
    }

    /**
     * Get all information about a video
     *
     * @param string $url    URL of page
     * @param string $format Format to use for the video
     *
     * @return object Decoded JSON
     * */
    public function getJSON($url, $format = null)
    {
        $this->procBuilder->setArguments(
            array(
                '--dump-json',
                $url
            )
        );
        if (isset($format)) {
            $this->procBuilder->add('-f '.$format);
        }
        $process = $this->procBuilder->getProcess();
        $process->run();
        if (!$process->isSuccessful()) {
            throw new \Exception($process->getErrorOutput());
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
    public function getURL($url, $format = null)
    {
        $this->procBuilder->setArguments(
            array(
                '--get-url',
                $url
            )
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
}

<?php
/**
 * PHP web interface for youtube-dl (http://rg3.github.com/youtube-dl/)
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
require_once 'config.php';
/**
 * PHP web interface for youtube-dl (http://rg3.github.com/youtube-dl/)
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
     * Get version of youtube-dl
     * 
     * @return string Version
     * */
    function getVersion ()
    {
        exec(
            PYTHON.' '.YOUTUBE_DL.' --version',
            $version, $code
        );
        return $version[0];
    }
    /**
     * Get the user agent used youtube-dl
     * 
     * @return string UA
     * */
    function getUA ()
    {
        exec(
            PYTHON.' '.YOUTUBE_DL.' --dump-user-agent',
            $version, $code
        );
        return $version[0];
    }
    
    /**
     * List all extractors
     * 
     * @return array Extractors
     * */
    function listExtractors ()
    {
        exec(
            PYTHON.' '.YOUTUBE_DL.' --list-extractors',
            $extractors, $code
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
    function getFilename ($url, $format=null)
    {
        $cmd=PYTHON.' youtube-dl';
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
     * Get title of video
     * 
     * @param string $url URL of page
     * 
     * @return string Title
     * */
    function getTitle ($url)
    {
        exec(
            PYTHON.' '.YOUTUBE_DL.' --get-title '.
            escapeshellarg($url),
            $title
        );
        $title=$title[0];
        return $title;
    }
    
    /**
     * Get all information about a video
     * 
     * @param string $url    URL of page
     * @param string $format Format to use for the video
     * 
     * @return string JSON
     * */
    function getJSON ($url, $format=null)
    {
        $cmd=PYTHON.' '.YOUTUBE_DL.' '.PARAMS;
        if (isset($format)) {
            $cmd .= ' -f '.escapeshellarg($format);
        }
        $cmd .=' --dump-json '.escapeshellarg($url)." 2>&1";
        exec(
            $cmd,
            $json, $code
        );
        if ($code>0) {
            return array('success'=>false, 'error'=>$json);
        } else {
            return json_decode($json[0]);
        }
    }

    /**
     * Get thumbnail of video
     * 
     * @param string $url URL of page
     * 
     * @return string URL of image
     * */
    function getThumbnail ($url)
    {
        exec(
            PYTHON.' '.YOUTUBE_DL.' --get-thumbnail '.
            escapeshellarg($url),
            $thumb
        );
        if (isset($thumb[0])) {
            return $thumb[0];
        }
    }
    
    /**
     * Get a list available formats for this video
     * 
     * @param string $url URL of page
     * 
     * @return string Title
     * */
    function getAvailableFormats ($url)
    {
        exec(
            PYTHON.' '.YOUTUBE_DL.' -F '.
            escapeshellarg($url),
            $formats
        );
        $return=array();
        foreach ($formats as $i=>$format) {
            if ($i > 4) {
                $return[]=(preg_split('/(\s\s+)|(\s+:?\s+)|(\s+\[)|\]/', $format));
            }
        }
        if (empty($return)) {
            foreach ($formats as $i=>$format) {
                if ($i > 3) {
                    $return[]=preg_split('/(\s\s+)|(\s+:?\s+)|(\s+\[)|\]/', $format);
                }
            }
        }
        return $return;
    }
    
    /**
     * Get URL of video from URL of page
     * 
     * @param string $url    URL of page
     * @param string $format Format to use for the video
     * 
     * @return string URL of video
     * */
    function getURL ($url, $format=null)
    {
        $cmd=PYTHON.' '.YOUTUBE_DL;
        if (isset($format)) {
            $cmd .= ' -f '.escapeshellarg($format);
        }
        $cmd .=' -g '.escapeshellarg($url)." 2>&1";
        exec(
            $cmd, $url, $code
        );
        if ($code>0) {
            return array('success'=>false, 'error'=>$url);
        } else {
            return array('success'=>true, 'url'=>end($url));
        }
        
    }
}

?>

<?php
/**
 * PHP web interface for youtube-dl (http://rg3.github.com/youtube-dl/)
 * Redirect to video in best format
 *
 * PHP Version 5.3.10
 *
 * @category Youtube-dl
 * @package  Youtubedl
 * @author   Pierre Rudloff <contact@rudloff.pro>
 * @license  GNU General Public License http://www.gnu.org/licenses/gpl.html
 * @link     http://rudloff.pro
 * */
require_once 'common.php';
require_once 'download.php';
if (isset($_GET["url"])) {
    $video = VideoDownload::getURL($_GET["url"]);
    if (isset($video['url'])) {
        header('Location: '.$video['url']);
    } else {
        echo "Can't find video";
    }
}

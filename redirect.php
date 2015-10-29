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
use Alltube\VideoDownload;
require_once 'common.php';
if (isset($_GET["url"])) {
    try {
        $video = VideoDownload::getURL($_GET["url"]);
        header('Location: '.$video['url']);
    } catch (Exception $e) {
        header('Content-Type: text/plain');
        echo $e->getMessage();
    }
}

<?php
/**
 * PHP web interface for youtube-dl (http://rg3.github.com/youtube-dl/)
 * JSON API
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
    header('Content-Type: application/json');
    $video = VideoDownload::getJSON($_GET["url"]);
    echo json_encode($video);
}

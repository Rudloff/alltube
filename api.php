<?php
/**
 * PHP web interface for youtube-dl (http://rg3.github.com/youtube-dl/)
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
$smarty->assign('class', 'video');
require_once 'download.php';
if (isset($_GET["url"])) {
    if (isset($_GET['audio'])) {
        $video = VideoDownload::getJSON($_GET["url"]);

        if (isset($video->url)) {
            //Vimeo needs a correct user-agent
            $UA = VideoDownload::getUA();
            ini_set(
                'user_agent',
                $UA
            );
            $url_info = parse_url($video->url);
            if ($url_info['scheme'] == 'rtmp') {
                header(
                    'Content-Disposition: attachment; filename="'.
                    html_entity_decode(
                        pathinfo(
                            VideoDownload::getFilename(
                                $video->webpage_url
                            ), PATHINFO_FILENAME
                        ).'.mp3', ENT_COMPAT, 'ISO-8859-1'
                    ).'"'
                );
                header("Content-Type: audio/mpeg");
                passthru(
                    '/usr/bin/rtmpdump -q -r '.escapeshellarg($video->url).
                    '   |  /usr/bin/avconv -v quiet -i - -f mp3 -vn pipe:1'
                );
                exit;
            } else {
                header(
                    'Content-Disposition: attachment; filename="'.
                    html_entity_decode(
                        pathinfo(
                            VideoDownload::getFilename(
                                $video->webpage_url
                            ), PATHINFO_FILENAME
                        ).'.mp3', ENT_COMPAT, 'ISO-8859-1'
                    ).'"'
                );
                header("Content-Type: audio/mpeg");
                passthru(
                    '/usr/bin/wget -q --user-agent='.escapeshellarg($UA).
                    '  -O - '.escapeshellarg($video->url).
                    '   |  /usr/bin/avconv -v quiet -i - -f mp3 -vn pipe:1'
                );
                exit;
            }
        } else {
            $error=true;
        }
    } else {
        $video = VideoDownload::getJSON($_GET["url"]);
        if (isset($video->webpage_url)) {
            $smarty->display('head.tpl');
            $smarty->assign('video', $video);
            $smarty->display('video.tpl');
            $smarty->display('footer.tpl');
        } else {
            $error=true;
        }
    }
}
if (isset($error)) {
    $smarty->display('head.tpl');
    $smarty->assign('errors', $video['error']);
    $smarty->display('error.tpl');
    $smarty->display('footer.tpl');
}

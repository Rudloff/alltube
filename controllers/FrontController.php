<?php
/**
 * FrontController class
 *
 * PHP Version 5.3.10
 *
 * @category Youtube-dl
 * @package  Youtubedl
 * @author   Pierre Rudloff <contact@rudloff.pro>
 * @license  GNU General Public License http://www.gnu.org/licenses/gpl.html
 * @link     http://rudloff.pro
 * */
namespace Alltube\Controller;
use Alltube\VideoDownload;
use Alltube\Config;
/**
 * Main controller
 *
 * PHP Version 5.3.10
 *
 * @category Youtube-dl
 * @package  Youtubedl
 * @author   Pierre Rudloff <contact@rudloff.pro>
 * @license  GNU General Public License http://www.gnu.org/licenses/gpl.html
 * @link     http://rudloff.pro
 * */
class FrontController
{

    /**
     * Display index page
     * @return void
     */
    static function index()
    {
        global $app;
        $config = Config::getInstance();
        $app->render(
            'head.tpl',
            array(
                'class'=>'index'
            )
        );
        $app->render(
            'header.tpl'
        );
        $app->render(
            'index.tpl',
            array(
                'convert'=>$config->convert
            )
        );
        $app->render('footer.tpl');
    }

    /**
     * Display a list of extractors
     * @return void
     */
    static function extractors()
    {
        global $app;
        $app->render(
            'head.tpl',
            array(
                'class'=>'extractors'
            )
        );
        $app->render('header.tpl');
        $app->render('logo.tpl');
        $app->render(
            'extractors.tpl',
            array(
                'extractors'=>VideoDownload::listExtractors()
            )
        );
        $app->render('footer.tpl');
    }

    /**
     * Dislay information about the video
     * @return void
     */
    static function video()
    {
        global $app;
        $config = Config::getInstance();
        if (isset($_GET["url"])) {
            if (isset($_GET['audio'])) {
                try {
                    $video = VideoDownload::getJSON($_GET["url"]);

                    //Vimeo needs a correct user-agent
                    $UA = VideoDownload::getUA();
                    ini_set(
                        'user_agent',
                        $UA
                    );
                    $url_info = parse_url($video->url);
                    if ($url_info['scheme'] == 'rtmp') {
                        ob_end_flush();
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
                            '   |  '.$config->avconv.
                            ' -v quiet -i - -f mp3 -vn pipe:1'
                        );
                        exit;
                    } else {
                        ob_end_flush();
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
                            'curl  --user-agent '.escapeshellarg($UA).
                            ' '.escapeshellarg($video->url).
                            '   |  '.$config->avconv.
                            ' -v quiet -i - -f mp3 -vn pipe:1'
                        );
                        exit;
                    }
                } catch (\Exception $e) {
                    $error = $e->getMessage();
                }
            } else {
                try {
                    $video = VideoDownload::getJSON($_GET["url"]);
                    $app->render(
                        'head.tpl',
                        array(
                            'class'=>'video'
                        )
                    );
                    $app->render(
                        'video.tpl',
                        array(
                            'video'=>$video
                        )
                    );
                    $app->render('footer.tpl');
                } catch (\Exception $e) {
                    $error = $e->getMessage();
                }
            }
        }
        if (isset($error)) {
            $app->render(
                'head.tpl',
                array(
                    'class'=>'video'
                )
            );
            $app->render(
                'error.tpl',
                array(
                    'errors'=>$error
                )
            );
            $app->render('footer.tpl');
        }
    }

    /**
     * Redirect to video file
     * @return void
     */
    static function redirect()
    {
        global $app;
        if (isset($_GET["url"])) {
            try {
                $video = VideoDownload::getURL($_GET["url"]);
                $app->redirect($video['url']);
            } catch (\Exception $e) {
                $app->response->headers->set('Content-Type', 'text/plain');
                echo $e->getMessage();
            }
        }
    }

    /**
     * Output JSON info about the video
     * @return void
     */
    static function json()
    {
        global $app;
        if (isset($_GET["url"])) {
            $app->response->headers->set('Content-Type', 'application/json');
            try {
                $video = VideoDownload::getJSON($_GET["url"]);
                echo json_encode($video);
            } catch (\Exception $e) {
                echo json_encode(array('success'=>false, 'error'=>$e->getMessage()));
            }
        }
    }
}

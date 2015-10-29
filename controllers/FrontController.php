<?php
namespace Alltube\Controller;
use Alltube\VideoDownload;

class FrontController {
    static function index() {
        global $app;
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
                'convert'=>CONVERT
            )
        );
        $app->render('footer.tpl');
    }

    static function extractors() {
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

    static function video() {
        global $app;
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
                            '   |  '.AVCONV.' -v quiet -i - -f mp3 -vn pipe:1'
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
                            'curl  --user-agent '.escapeshellarg($UA).
                            ' '.escapeshellarg($video->url).
                            '   |  '.AVCONV.' -v quiet -i - -f mp3 -vn pipe:1'
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
}

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
     *
     * @param Request  $request  PSR-7 request
     * @param Response $response PSR-7 response
     *
     * @return void
     */
    public static function index($request, $response)
    {
        global $container;
        $config = Config::getInstance();
        $container->view->render(
            $response,
            'head.tpl',
            array(
                'class'=>'index'
            )
        );
        $container->view->render(
            $response,
            'header.tpl'
        );
        $container->view->render(
            $response,
            'index.tpl',
            array(
                'convert'=>$config->convert
            )
        );
        $container->view->render($response, 'footer.tpl');
    }

    /**
     * Display a list of extractors
     *
     * @param Request  $request  PSR-7 request
     * @param Response $response PSR-7 response
     *
     * @return void
     */
    public static function extractors($request, $response)
    {
        global $container;
        $container->view->render(
            $response,
            'head.tpl',
            array(
                'class'=>'extractors'
            )
        );
        $container->view->render($response, 'header.tpl');
        $container->view->render($response, 'logo.tpl');
        $container->view->render(
            $response,
            'extractors.tpl',
            array(
                'extractors'=>VideoDownload::listExtractors()
            )
        );
        $container->view->render($response, 'footer.tpl');
    }

    /**
     * Dislay information about the video
     *
     * @param Request  $request  PSR-7 request
     * @param Response $response PSR-7 response
     *
     * @return void
     */
    public static function video($request, $response)
    {
        global $container;
        $params = $request->getQueryParams();
        $config = Config::getInstance();
        if (isset($params["url"])) {
            if (isset($params['audio'])) {
                try {
                    $video = VideoDownload::getJSON($params["url"]);

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
                                    ),
                                    PATHINFO_FILENAME
                                ).'.mp3',
                                ENT_COMPAT,
                                'ISO-8859-1'
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
                                    ),
                                    PATHINFO_FILENAME
                                ).'.mp3',
                                ENT_COMPAT,
                                'ISO-8859-1'
                            ).'"'
                        );
                        header("Content-Type: audio/mpeg");
                        passthru(
                            'curl '.$config->curl_params.
                            ' --user-agent '.escapeshellarg($UA).
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
                    $video = VideoDownload::getJSON($params["url"]);
                    $container->view->render(
                        $response,
                        'head.tpl',
                        array(
                            'class'=>'video'
                        )
                    );
                    $container->view->render(
                        $response,
                        'video.tpl',
                        array(
                            'video'=>$video
                        )
                    );
                    $container->view->render($response, 'footer.tpl');
                } catch (\Exception $e) {
                    $error = $e->getMessage();
                }
            }
        }
        if (isset($error)) {
            $container->view->render(
                $response,
                'head.tpl',
                array(
                    'class'=>'video'
                )
            );
            $container->view->render(
                $response,
                'error.tpl',
                array(
                    'errors'=>$error
                )
            );
            $container->view->render($response, 'footer.tpl');
        }
    }

    /**
     * Redirect to video file
     *
     * @param Request  $request  PSR-7 request
     * @param Response $response PSR-7 response
     *
     * @return void
     */
    public static function redirect($request, $response)
    {
        global $app;
        $params = $request->getQueryParams();
        if (isset($params["url"])) {
            try {
                $format = isset($params["format"]) ? $params["format"] : 'best';
                $video = VideoDownload::getJSON($params["url"], $format);
                $client = new \GuzzleHttp\Client();
                $stream = $client->request('GET', $video->url, array('stream'=>true));
                $response = $response->withHeader('Content-Disposition', 'inline; filename="'.$video->_filename.'"');
                $response = $response->withHeader('Content-Type', $stream->getHeader('Content-Type'));
                $response = $response->withHeader('Content-Length', $stream->getHeader('Content-Length'));
                if ($request->isGet()) {
                    $response = $response->withBody($stream->getBody());
                }
                return $response;
            } catch (\Exception $e) {
                $response->getBody()->write($e->getMessage().PHP_EOL);
                return $response->withHeader('Content-Type', 'text/plain');
            }
        }
    }

    /**
     * Output JSON info about the video
     *
     * @param Request  $request  PSR-7 request
     * @param Response $response PSR-7 response
     *
     * @return void
     */
    public static function json($request, $response)
    {
        global $app;
        $params = $request->getQueryParams();
        if (isset($params["url"])) {
            try {
                $video = VideoDownload::getJSON($params["url"]);
                return $response->withJson($video);
            } catch (\Exception $e) {
                return $response->withJson(
                    array('success'=>false, 'error'=>$e->getMessage())
                );
            }
        }
    }
}

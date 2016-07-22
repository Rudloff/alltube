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
use Symfony\Component\Process\ProcessBuilder;
use Chain\Chain;
use Slim\Http\Stream;
use Slim\Http\Request;
use Slim\Http\Response;

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
    public function __construct()
    {
        $this->config = Config::getInstance();
        $this->download = new VideoDownload();
    }

    /**
     * Display index page
     *
     * @param Request  $request  PSR-7 request
     * @param Response $response PSR-7 response
     *
     * @return void
     */
    public function index(Request $request, Response $response)
    {
        global $container;
        $container->view->render(
            $response,
            'head.tpl',
            array(
                'class'=>'index',
                'description'=>'Easily download videos from Youtube, Dailymotion, Vimeo and other websites.'
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
                'convert'=>$this->config->convert
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
    public function extractors(Request $request, Response $response)
    {
        global $container;
        $container->view->render(
            $response,
            'head.tpl',
            array(
                'class'=>'extractors',
                'title'=>'Supported websites',
                'description'
                    =>'List of all supported websites from which Alltube Download can extract video or audio files'
            )
        );
        $container->view->render($response, 'header.tpl');
        $container->view->render($response, 'logo.tpl');
        $container->view->render(
            $response,
            'extractors.tpl',
            array(
                'extractors'=>$this->download->listExtractors()
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
    public function video(Request $request, Response $response)
    {
        global $container;
        $params = $request->getQueryParams();
        $this->config = Config::getInstance();
        if (isset($params["url"])) {
            if (isset($params['audio'])) {
                try {
                    $url = $this->download->getURL($params["url"], 'mp3[protocol^=http]');
                    return $response->withRedirect($url);
                } catch (\Exception $e) {
                    $video = $this->download->getJSON($params["url"], 'bestaudio/best');

                    if (!shell_exec('which '.$this->config->avconv)) {
                        throw(new \Exception('Can\'t find avconv or ffmpeg'));
                    }

                    $avconvProc = ProcessBuilder::create(
                        array(
                            $this->config->avconv,
                            '-v', 'quiet',
                            '-i', '-',
                            '-f', 'mp3',
                            '-vn',
                            'pipe:1'
                        )
                    );

                    //Vimeo needs a correct user-agent
                    ini_set(
                        'user_agent',
                        $video->http_headers->{'User-Agent'}
                    );

                    $response = $response->withHeader(
                        'Content-Disposition',
                        'attachment; filename="'.
                        html_entity_decode(
                            pathinfo(
                                $video->_filename,
                                PATHINFO_FILENAME
                            ).'.mp3',
                            ENT_COMPAT,
                            'ISO-8859-1'
                        ).'"'
                    );
                    $response = $response->withHeader('Content-Type', 'audio/mpeg');

                    if (parse_url($video->url, PHP_URL_SCHEME) == 'rtmp') {
                        if (!shell_exec('which '.$this->config->rtmpdump)) {
                            throw(new \Exception('Can\'t find rtmpdump'));
                        }
                        $builder = new ProcessBuilder(
                            array(
                                $this->config->rtmpdump,
                                '-q',
                                '-r',
                                $video->url,
                                '--pageUrl', $video->webpage_url
                            )
                        );
                        if (isset($video->player_url)) {
                            $builder->add('--swfVfy');
                            $builder->add($video->player_url);
                        }
                        if (isset($video->flash_version)) {
                            $builder->add('--flashVer');
                            $builder->add($video->flash_version);
                        }
                        if (isset($video->play_path)) {
                            $builder->add('--playpath');
                            $builder->add($video->play_path);
                        }
                        foreach ($video->rtmp_conn as $conn) {
                            $builder->add('--conn');
                            $builder->add($conn);
                        }
                        $chain = new Chain($builder->getProcess());
                        $chain->add('|', $avconvProc);
                    } else {
                        if (!shell_exec('which curl')) {
                            throw(new \Exception('Can\'t find curl'));
                        }
                        $chain = new Chain(
                            ProcessBuilder::create(
                                array_merge(
                                    array(
                                        'curl',
                                        '--silent',
                                        '--user-agent', $video->http_headers->{'User-Agent'},
                                        $video->url
                                    ),
                                    $this->config->curl_params
                                )
                            )
                        );
                        $chain->add('|', $avconvProc);
                    }
                    if ($request->isGet()) {
                        $response = $response->withBody(new Stream(popen($chain->getProcess()->getCommandLine(), 'r')));
                    }
                    return $response;
                }
            } else {
                $video = $this->download->getJSON($params["url"]);
                $container->view->render(
                    $response,
                    'head.tpl',
                    array(
                        'class'=>'video',
                        'title'=>$video->title,
                        'description'=>'Download "'.$video->title.'" from '.$video->extractor_key
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
            }
        } else {
            return $response->withRedirect($container->get('router')->pathFor('index'));
        }
    }

    public function error(Request $request, Response $response, \Exception $exception)
    {
        global $container;
        $container->view->render(
            $response,
            'head.tpl',
            array(
                'class'=>'video',
                'title'=>'Error'
            )
        );
        $container->view->render(
            $response,
            'error.tpl',
            array(
                'errors'=>$exception->getMessage()
            )
        );
        $container->view->render($response, 'footer.tpl');
        return $response->withStatus(500);
    }

    /**
     * Redirect to video file
     *
     * @param Request  $request  PSR-7 request
     * @param Response $response PSR-7 response
     *
     * @return void
     */
    public function redirect(Request $request, Response $response)
    {
        global $app;
        $params = $request->getQueryParams();
        if (isset($params["url"])) {
            try {
                $url = $this->download->getURL($params["url"], $params["format"]);
                return $response->withRedirect($url);
            } catch (\Exception $e) {
                $response->getBody()->write($e->getMessage());
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
    public function json(Request $request, Response $response)
    {
        global $app;
        $params = $request->getQueryParams();
        if (isset($params["url"])) {
            try {
                $video = $this->download->getJSON($params["url"]);
                return $response->withJson($video);
            } catch (\Exception $e) {
                return $response->withJson(
                    array('success'=>false, 'error'=>$e->getMessage())
                );
            }
        }
    }
}

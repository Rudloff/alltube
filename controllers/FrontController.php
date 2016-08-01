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
use Slim\Http\Stream;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Container;

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
    public function __construct(Container $container)
    {
        $this->config = Config::getInstance();
        $this->download = new VideoDownload();
        $this->container = $container;
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
        $this->container->view->render(
            $response,
            'index.tpl',
            array(
                'convert'=>$this->config->convert,
                'class'=>'index',
                'description'=>'Easily download videos from Youtube, Dailymotion, Vimeo and other websites.'
            )
        );
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
        $this->container->view->render(
            $response,
            'extractors.tpl',
            array(
                'extractors'=>$this->download->listExtractors(),
                'class'=>'extractors',
                'title'=>'Supported websites',
                'description'
                    =>'List of all supported websites from which Alltube Download can extract video or audio files'
            )
        );
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
        $params = $request->getQueryParams();
        $this->config = Config::getInstance();
        if (isset($params["url"])) {
            if (isset($params['audio'])) {
                try {
                    $url = $this->download->getURL($params["url"], 'mp3[protocol^=http]');
                    return $response->withRedirect($url);
                } catch (\Exception $e) {
                    $response = $response->withHeader(
                        'Content-Disposition',
                        'attachment; filename="'.
                        $this->download->getAudioFilename($params["url"], 'bestaudio/best').'"'
                    );
                    $response = $response->withHeader('Content-Type', 'audio/mpeg');

                    if ($request->isGet()) {
                        $process = $this->download->getAudioStream($params["url"], 'bestaudio/best');
                        $response = $response->withBody(new Stream($process));
                    }
                    return $response;
                }
            } else {
                $video = $this->download->getJSON($params["url"]);
                $this->container->view->render(
                    $response,
                    'video.tpl',
                    array(
                        'video'=>$video,
                        'class'=>'video',
                        'title'=>$video->title,
                        'description'=>'Download "'.$video->title.'" from '.$video->extractor_key
                    )
                );
            }
        } else {
            return $response->withRedirect($this->container->get('router')->pathFor('index'));
        }
    }

    public function error(Request $request, Response $response, \Exception $exception)
    {
        $this->container->view->render(
            $response,
            'error.tpl',
            array(
                'errors'=>$exception->getMessage(),
                'class'=>'video',
                'title'=>'Error'
            )
        );
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

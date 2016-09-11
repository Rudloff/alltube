<?php
/**
 * FrontController class.
 */
namespace Alltube\Controller;

use Alltube\Config;
use Alltube\VideoDownload;
use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Http\Stream;

/**
 * Main controller.
 */
class FrontController
{
    /**
     * Config instance.
     *
     * @var Config
     */
    private $config;

    /**
     * VideoDownload instance.
     *
     * @var VideoDownload
     */
    private $download;

    /**
     * Slim dependency container.
     *
     * @var Container
     */
    private $container;

    /**
     * FrontController constructor.
     *
     * @param Container $container Slim dependency container
     */
    public function __construct(Container $container)
    {
        $this->config = Config::getInstance();
        $this->download = new VideoDownload();
        $this->container = $container;
    }

    /**
     * Display index page.
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
            [
                'convert'     => $this->config->convert,
                'class'       => 'index',
                'description' => 'Easily download videos from Youtube, Dailymotion, Vimeo and other websites.',
            ]
        );
    }

    /**
     * Display a list of extractors.
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
            [
                'extractors'  => $this->download->listExtractors(),
                'class'       => 'extractors',
                'title'       => 'Supported websites',
                'description' => 'List of all supported websites from which Alltube Download can extract video or audio files',
            ]
        );
    }

    /**
     * Dislay information about the video.
     *
     * @param Request  $request  PSR-7 request
     * @param Response $response PSR-7 response
     *
     * @return Response HTTP response
     */
    public function video(Request $request, Response $response)
    {
        $params = $request->getQueryParams();
        $this->config = Config::getInstance();
        if (isset($params['url'])) {
            if (isset($params['audio'])) {
                try {
                    return $this->getStream($params["url"], 'mp3[protocol^=http]', $response, $request);
                } catch (\Exception $e) {
                    $response = $response->withHeader(
                        'Content-Disposition',
                        'attachment; filename="'.
                        $this->download->getAudioFilename($params['url'], 'bestaudio/best').'"'
                    );
                    $response = $response->withHeader('Content-Type', 'audio/mpeg');

                    if ($request->isGet()) {
                        $process = $this->download->getAudioStream($params['url'], 'bestaudio/best');
                        $response = $response->withBody(new Stream($process));
                    }

                    return $response;
                }
            } else {
                $video = $this->download->getJSON($params['url']);
                $this->container->view->render(
                    $response,
                    'video.tpl',
                    [
                        'video'       => $video,
                        'class'       => 'video',
                        'title'       => $video->title,
                        'description' => 'Download "'.$video->title.'" from '.$video->extractor_key,
                    ]
                );
            }
        } else {
            return $response->withRedirect($this->container->get('router')->pathFor('index'));
        }
    }

    /**
     * Display an error page.
     *
     * @param Request    $request   PSR-7 request
     * @param Response   $response  PSR-7 response
     * @param \Exception $exception Error to display
     *
     * @return Response HTTP response
     */
    public function error(Request $request, Response $response, \Exception $exception)
    {
        $this->container->view->render(
            $response,
            'error.tpl',
            [
                'errors' => $exception->getMessage(),
                'class'  => 'video',
                'title'  => 'Error',
            ]
        );

        return $response->withStatus(500);
    }

    private function getStream($url, $format, $response, $request)
    {
        if (!isset($format)) {
            $format = 'best';
        }
        $video = $this->download->getJSON($url, $format);
        $client = new \GuzzleHttp\Client();
        $stream = $client->request('GET', $video->url, array('stream'=>true));
        $response = $response->withHeader('Content-Disposition', 'attachment; filename="'.$video->_filename.'"');
        $response = $response->withHeader('Content-Type', $stream->getHeader('Content-Type'));
        $response = $response->withHeader('Content-Length', $stream->getHeader('Content-Length'));
        if ($request->isGet()) {
            $response = $response->withBody($stream->getBody());
        }
        return $response;
    }

    /**
     * Redirect to video file.
     *
     * @param Request  $request  PSR-7 request
     * @param Response $response PSR-7 response
     *
     * @return Response HTTP response
     */
    public function redirect(Request $request, Response $response)
    {
        $params = $request->getQueryParams();
        if (isset($params['url'])) {
            try {
                return $this->getStream($params["url"], $params["format"], $response, $request);
            } catch (\Exception $e) {
                $response->getBody()->write($e->getMessage());

                return $response->withHeader('Content-Type', 'text/plain');
            }
        }
    }

    /**
     * Output JSON info about the video.
     *
     * @param Request  $request  PSR-7 request
     * @param Response $response PSR-7 response
     *
     * @return Response HTTP response
     */
    public function json(Request $request, Response $response)
    {
        $params = $request->getQueryParams();
        if (isset($params['url'])) {
            try {
                $video = $this->download->getJSON($params['url']);

                return $response->withJson($video);
            } catch (\Exception $e) {
                return $response->withJson(
                    ['success' => false, 'error' => $e->getMessage()]
                );
            }
        }
    }
}

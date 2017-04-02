<?php
/**
 * FrontController class.
 */

namespace Alltube\Controller;

use Alltube\Config;
use Alltube\PasswordException;
use Alltube\VideoDownload;
use Interop\Container\ContainerInterface;
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
     * @var ContainerInterface
     */
    private $container;

    /**
     * Session segment used to store session variables.
     *
     * @var \Aura\Session\Segment
     */
    private $sessionSegment;

    /**
     * Smarty view.
     *
     * @var \Slim\Views\Smarty
     */
    private $view;

    /**
     * Default youtube-dl format.
     *
     * @var string
     */
    private $defaultFormat = 'best[protocol^=http]';

    /**
     * FrontController constructor.
     *
     * @param Container $container Slim dependency container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->config = Config::getInstance();
        $this->download = new VideoDownload();
        $this->container = $container;
        $this->view = $this->container->get('view');
        $session_factory = new \Aura\Session\SessionFactory();
        $session = $session_factory->newInstance($_COOKIE);
        $this->sessionSegment = $session->getSegment('Alltube\Controller\FrontController');
        if ($this->config->stream) {
            $this->defaultFormat = 'best';
        }
    }

    /**
     * Display index page.
     *
     * @param Request  $request  PSR-7 request
     * @param Response $response PSR-7 response
     *
     * @return Response HTTP response
     */
    public function index(Request $request, Response $response)
    {
        $uri = $request->getUri();
        $this->view->render(
            $response,
            'index.tpl',
            [
                'convert'      => $this->config->convert,
                'uglyUrls'     => $this->config->uglyUrls,
                'class'        => 'index',
                'description'  => 'Easily download videos from Youtube, Dailymotion, Vimeo and other websites.',
                'domain'       => $uri->getScheme().'://'.$uri->getAuthority(),
                'canonical'    => $this->getCanonicalUrl($request),
            ]
        );

        return $response;
    }

    /**
     * Display a list of extractors.
     *
     * @param Request  $request  PSR-7 request
     * @param Response $response PSR-7 response
     *
     * @return Response HTTP response
     */
    public function extractors(Request $request, Response $response)
    {
        $this->view->render(
            $response,
            'extractors.tpl',
            [
                'extractors'  => $this->download->listExtractors(),
                'class'       => 'extractors',
                'title'       => 'Supported websites',
                'description' => 'List of all supported websites from which Alltube Download '.
                    'can extract video or audio files',
                'canonical'   => $this->getCanonicalUrl($request),
            ]
        );

        return $response;
    }

    /**
     * Display a password prompt.
     *
     * @param Request  $request  PSR-7 request
     * @param Response $response PSR-7 response
     *
     * @return Response HTTP response
     */
    public function password(Request $request, Response $response)
    {
        $this->view->render(
            $response,
            'password.tpl',
            [
                'class'       => 'password',
                'title'       => 'Password prompt',
                'description' => 'You need a password in order to download this video with Alltube Download',
                'canonical'   => $this->getCanonicalUrl($request),
            ]
        );

        return $response;
    }

    /**
     * Return the converted MP3 file.
     *
     * @param Request  $request  PSR-7 request
     * @param Response $response PSR-7 response
     * @param array    $params   GET query parameters
     * @param string   $password Video password
     *
     * @return Response HTTP response
     */
    private function getAudioResponse(Request $request, Response $response, array $params, $password = null)
    {
        try {
            if ($this->config->stream) {
                return $this->getStream($params['url'], 'mp3', $response, $request, $password);
            } else {
                $url = $this->download->getURL($params['url'], 'mp3[protocol^=http]', $password);

                return $response->withRedirect($url);
            }
        } catch (PasswordException $e) {
            return $this->password($request, $response);
        } catch (\Exception $e) {
            $response = $response->withHeader(
                'Content-Disposition',
                'attachment; filename="'.
                $this->download->getAudioFilename($params['url'], 'bestaudio/best', $password).'"'
            );
            $response = $response->withHeader('Content-Type', 'audio/mpeg');

            if ($request->isGet() || $request->isPost()) {
                $process = $this->download->getAudioStream($params['url'], 'bestaudio/best', $password);
                $response = $response->withBody(new Stream($process));
            }

            return $response;
        }
    }

    /**
     * Return the video description page.
     *
     * @param Request  $request  PSR-7 request
     * @param Response $response PSR-7 response
     * @param array    $params   GET query parameters
     * @param string   $password Video password
     *
     * @return Response HTTP response
     */
    private function getVideoResponse(Request $request, Response $response, array $params, $password = null)
    {
        try {
            $video = $this->download->getJSON($params['url'], $this->defaultFormat, $password);
        } catch (PasswordException $e) {
            return $this->password($request, $response);
        }
        if ($this->config->stream) {
            $protocol = '';
        } else {
            $protocol = '[protocol^=http]';
        }
        $this->view->render(
            $response,
            'video.tpl',
            [
                'video'       => $video,
                'class'       => 'video',
                'title'       => $video->title,
                'description' => 'Download "'.$video->title.'" from '.$video->extractor_key,
                'protocol'    => $protocol,
                'config'      => $this->config,
                'canonical'   => $this->getCanonicalUrl($request),
                'uglyUrls'     => $this->config->uglyUrls,
            ]
        );

        return $response;
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
        if (isset($params['url'])) {
            $password = $request->getParam('password');
            if (isset($password)) {
                $this->sessionSegment->setFlash($params['url'], $password);
            }
            if (isset($params['audio'])) {
                return $this->getAudioResponse($request, $response, $params, $password);
            } else {
                return $this->getVideoResponse($request, $response, $params, $password);
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
        $this->view->render(
            $response,
            'error.tpl',
            [
                'errors'    => $exception->getMessage(),
                'class'     => 'video',
                'title'     => 'Error',
                'canonical' => $this->getCanonicalUrl($request),
            ]
        );

        return $response->withStatus(500);
    }

    /**
     * Get a video/audio stream piped through the server.
     *
     * @param string   $url      URL of the video
     * @param string   $format   Requested format
     * @param Response $response PSR-7 response
     * @param Request  $request  PSR-7 request
     * @param string   $password Video password
     *
     * @return Response HTTP response
     */
    private function getStream($url, $format, $response, $request, $password = null)
    {
        $video = $this->download->getJSON($url, $format, $password);
        if ($video->protocol == 'm3u8') {
            $stream = $this->download->getM3uStream($video);
            $response = $response->withHeader('Content-Type', 'video/'.$video->ext);
            if ($request->isGet()) {
                $response = $response->withBody(new Stream($stream));
            }
        } else {
            $client = new \GuzzleHttp\Client();
            $stream = $client->request('GET', $video->url, ['stream' => true]);
            $response = $response->withHeader('Content-Type', $stream->getHeader('Content-Type'));
            $response = $response->withHeader('Content-Length', $stream->getHeader('Content-Length'));
            if ($request->isGet()) {
                $response = $response->withBody($stream->getBody());
            }
        }
        $response = $response->withHeader('Content-Disposition', 'attachment; filename="'.$video->_filename.'"');

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
        if (isset($params['format'])) {
            $format = $params['format'];
        } else {
            $format = $this->defaultFormat;
        }
        if (isset($params['url'])) {
            try {
                if ($this->config->stream) {
                    return $this->getStream(
                        $params['url'],
                        $format,
                        $response,
                        $request,
                        $this->sessionSegment->getFlash($params['url'])
                    );
                } else {
                    $url = $this->download->getURL(
                        $params['url'],
                        $format,
                        $this->sessionSegment->getFlash($params['url'])
                    );

                    return $response->withRedirect($url);
                }
            } catch (PasswordException $e) {
                return $response->withRedirect(
                    $this->container->get('router')->pathFor('video').'?url='.urlencode($params['url'])
                );
            } catch (\Exception $e) {
                $response->getBody()->write($e->getMessage());

                return $response->withHeader('Content-Type', 'text/plain')->withStatus(500);
            }
        } else {
            return $response->withRedirect($this->container->get('router')->pathFor('index'));
        }
    }

    /**
     * Generate the canonical URL of the current page.
     *
     * @param Request $request PSR-7 Request
     *
     * @return string URL
     */
    private function getCanonicalUrl(Request $request)
    {
        $uri = $request->getUri();
        $return = 'https://alltubedownload.net/';

        $path = $uri->getPath();
        if ($path != '/') {
            $return .= $path;
        }

        $query = $uri->getQuery();
        if (!empty($query)) {
            $return .= '?'.$query;
        }

        return $return;
    }
}

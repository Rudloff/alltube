<?php
/**
 * FrontController class.
 */

namespace Alltube\Controller;

use Alltube\Config;
use Alltube\EmptyUrlException;
use Alltube\Locale;
use Alltube\LocaleManager;
use Alltube\PasswordException;
use Alltube\PlaylistArchiveStream;
use Alltube\Video;
use Aura\Session\Segment;
use Aura\Session\SessionFactory;
use Exception;
use GuzzleHttp\Client;
use Psr\Container\ContainerInterface;
use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Http\Stream;
use Slim\Views\Smarty;

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
     * Current video.
     *
     * @var Video
     */
    private $video;

    /**
     * Slim dependency container.
     *
     * @var ContainerInterface
     */
    private $container;

    /**
     * Session segment used to store session variables.
     *
     * @var Segment
     */
    private $sessionSegment;

    /**
     * Smarty view.
     *
     * @var Smarty
     */
    private $view;

    /**
     * Default youtube-dl format.
     *
     * @var string
     */
    private $defaultFormat = 'best[protocol=https]/best[protocol=http]';

    /**
     * LocaleManager instance.
     *
     * @var LocaleManager
     */
    private $localeManager;

    /**
     * FrontController constructor.
     *
     * @param ContainerInterface $container Slim dependency container
     * @param array              $cookies   Cookie array
     */
    public function __construct(ContainerInterface $container, array $cookies = [])
    {
        $this->config = Config::getInstance();
        $this->container = $container;
        $this->view = $this->container->get('view');
        $this->localeManager = $this->container->get('locale');
        $session_factory = new SessionFactory();
        $session = $session_factory->newInstance($cookies);
        $this->sessionSegment = $session->getSegment(self::class);
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
        $uri = $request->getUri()->withUserInfo('');
        $this->view->render(
            $response,
            'index.tpl',
            [
                'config'           => $this->config,
                'class'            => 'index',
                'description'      => _('Easily download videos from Youtube, Dailymotion, Vimeo and other websites.'),
                'domain'           => $uri->getScheme().'://'.$uri->getAuthority(),
                'canonical'        => $this->getCanonicalUrl($request),
                'supportedLocales' => $this->localeManager->getSupportedLocales(),
                'locale'           => $this->localeManager->getLocale(),
            ]
        );

        return $response;
    }

    /**
     * Switch locale.
     *
     * @param Request  $request  PSR-7 request
     * @param Response $response PSR-7 response
     * @param array    $data     Query parameters
     *
     * @return Response
     */
    public function locale(Request $request, Response $response, array $data)
    {
        $this->localeManager->setLocale(new Locale($data['locale']));

        return $response->withRedirect($this->container->get('router')->pathFor('index'));
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
                'config'      => $this->config,
                'extractors'  => Video::getExtractors(),
                'class'       => 'extractors',
                'title'       => _('Supported websites'),
                'description' => _('List of all supported websites from which Alltube Download '.
                    'can extract video or audio files'),
                'canonical' => $this->getCanonicalUrl($request),
                'locale'    => $this->localeManager->getLocale(),
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
                'config'      => $this->config,
                'class'       => 'password',
                'title'       => _('Password prompt'),
                'description' => _('You need a password in order to download this video with Alltube Download'),
                'canonical'   => $this->getCanonicalUrl($request),
                'locale'      => $this->localeManager->getLocale(),
            ]
        );

        return $response;
    }

    /**
     * Return a converted MP3 file.
     *
     * @param Request  $request  PSR-7 request
     * @param Response $response PSR-7 response
     *
     * @return Response HTTP response
     */
    private function getConvertedAudioResponse(Request $request, Response $response)
    {
        $from = $request->getQueryParam('from');
        $to = $request->getQueryParam('to');

        $response = $response->withHeader(
            'Content-Disposition',
            'attachment; filename="'.
            $this->video->getFileNameWithExtension('mp3').'"'
        );
        $response = $response->withHeader('Content-Type', 'audio/mpeg');

        if ($request->isGet() || $request->isPost()) {
            try {
                $process = $this->video->getAudioStream($from, $to);
            } catch (Exception $e) {
                // Fallback to default format.
                $this->video = $this->video->withFormat($this->defaultFormat);
                $process = $this->video->getAudioStream($from, $to);
            }
            $response = $response->withBody(new Stream($process));
        }

        return $response;
    }

    /**
     * Return the MP3 file.
     *
     * @param Request  $request  PSR-7 request
     * @param Response $response PSR-7 response
     *
     * @return Response HTTP response
     */
    private function getAudioResponse(Request $request, Response $response)
    {
        try {
            // First, we try to get a MP3 file directly.
            if (!empty($request->getQueryParam('from')) || !empty($request->getQueryParam('to'))) {
                throw new Exception('Force convert when we need to seek.');
            }

            if ($this->config->stream) {
                $this->video = $this->video->withFormat('mp3');

                return $this->getStream($request, $response);
            } else {
                $this->video = $this->video->withFormat('mp3[protocol=https]/mp3[protocol=http]');

                $urls = $this->video->getUrl();

                return $response->withRedirect($urls[0]);
            }
        } catch (PasswordException $e) {
            return $this->password($request, $response);
        } catch (Exception $e) {
            // If MP3 is not available, we convert it.
            $this->video = $this->video->withFormat($this->defaultFormat);

            return $this->getConvertedAudioResponse($request, $response);
        }
    }

    /**
     * Return the video description page.
     *
     * @param Request  $request  PSR-7 request
     * @param Response $response PSR-7 response
     *
     * @return Response HTTP response
     */
    private function getVideoResponse(Request $request, Response $response)
    {
        try {
            $this->video->getJson();
        } catch (PasswordException $e) {
            return $this->password($request, $response);
        }

        if (isset($this->video->entries)) {
            $template = 'playlist.tpl';
        } else {
            $template = 'video.tpl';
        }
        $title = _('Video download');
        $description = _('Download video from ').$this->video->extractor_key;
        if (isset($this->video->title)) {
            $title = $this->video->title;
            $description = _('Download').' "'.$this->video->title.'" '._('from').' '.$this->video->extractor_key;
        }
        $this->view->render(
            $response,
            $template,
            [
                'video'         => $this->video,
                'class'         => 'video',
                'title'         => $title,
                'description'   => $description,
                'config'        => $this->config,
                'canonical'     => $this->getCanonicalUrl($request),
                'locale'        => $this->localeManager->getLocale(),
                'defaultFormat' => $this->defaultFormat,
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
        $url = $request->getQueryParam('url') ?: $request->getQueryParam('v');

        if (isset($url) && !empty($url)) {
            $password = $request->getParam('password');
            if (isset($password)) {
                $this->sessionSegment->setFlash($url, $password);
            }

            $this->video = new Video($url, $this->defaultFormat, $password);

            if ($request->getQueryParam('audio')) {
                return $this->getAudioResponse($request, $response);
            } else {
                return $this->getVideoResponse($request, $response);
            }
        } else {
            return $response->withRedirect($this->container->get('router')->pathFor('index'));
        }
    }

    /**
     * Display an error page.
     *
     * @param Request   $request   PSR-7 request
     * @param Response  $response  PSR-7 response
     * @param Exception $exception Error to display
     *
     * @return Response HTTP response
     */
    public function error(Request $request, Response $response, Exception $exception)
    {
        $this->view->render(
            $response,
            'error.tpl',
            [
                'config'    => $this->config,
                'errors'    => $exception->getMessage(),
                'class'     => 'video',
                'title'     => _('Error'),
                'canonical' => $this->getCanonicalUrl($request),
                'locale'    => $this->localeManager->getLocale(),
            ]
        );

        return $response->withStatus(500);
    }

    /**
     * Get a video/audio stream piped through the server.
     *
     * @param Response $response PSR-7 response
     * @param Request  $request  PSR-7 request
     *
     * @return Response HTTP response
     */
    private function getStream(Request $request, Response $response)
    {
        if (isset($this->video->entries)) {
            $stream = new PlaylistArchiveStream($this->video);
            $response = $response->withHeader('Content-Type', 'application/x-tar');
            $response = $response->withHeader(
                'Content-Disposition',
                'attachment; filename="'.$this->video->title.'.tar"'
            );

            return $response->withBody($stream);
        } elseif ($this->video->protocol == 'rtmp') {
            $response = $response->withHeader('Content-Type', 'video/'.$this->video->ext);
            $body = new Stream($this->video->getRtmpStream());
        } elseif ($this->video->protocol == 'm3u8' || $this->video->protocol == 'm3u8_native') {
            $response = $response->withHeader('Content-Type', 'video/'.$this->video->ext);
            $body = new Stream($this->video->getM3uStream());
        } else {
            $client = new Client();
            $stream = $client->request(
                'GET',
                $this->video->getUrl(),
                [
                    'stream'  => true,
                    'headers' => ['Range' => $request->getHeader('Range')],
                ]
            );
            $response = $response->withHeader('Content-Type', $stream->getHeader('Content-Type'));
            $response = $response->withHeader('Content-Length', $stream->getHeader('Content-Length'));
            $response = $response->withHeader('Accept-Ranges', $stream->getHeader('Accept-Ranges'));
            $response = $response->withHeader('Content-Range', $stream->getHeader('Content-Range'));
            if ($stream->getStatusCode() == 206) {
                $response = $response->withStatus(206);
            }
            $body = $stream->getBody();
        }
        if ($request->isGet()) {
            $response = $response->withBody($body);
        }
        $response = $response->withHeader(
            'Content-Disposition',
            'attachment; filename="'.
                $this->video->getFilename().'"'
        );

        return $response;
    }

    /**
     * Get a remuxed stream piped through the server.
     *
     * @param Response $response PSR-7 response
     * @param Request  $request  PSR-7 request
     *
     * @return Response HTTP response
     */
    private function getRemuxStream(Request $request, Response $response)
    {
        if (!$this->config->remux) {
            throw new Exception(_('You need to enable remux mode to merge two formats.'));
        }
        $stream = $this->video->getRemuxStream();
        $response = $response->withHeader('Content-Type', 'video/x-matroska');
        if ($request->isGet()) {
            $response = $response->withBody(new Stream($stream));
        }

        return $response->withHeader(
            'Content-Disposition',
            'attachment; filename="'.$this->video->getFileNameWithExtension('mkv')
        );
    }

    /**
     * Get video format from request parameters or default format if none is specified.
     *
     * @param Request $request PSR-7 request
     *
     * @return string format
     */
    private function getFormat(Request $request)
    {
        $format = $request->getQueryParam('format');
        if (!isset($format)) {
            $format = $this->defaultFormat;
        }

        return $format;
    }

    /**
     * Get approriate HTTP response to redirect query
     * Depends on whether we want to stream, remux or simply redirect.
     *
     * @param Response $response PSR-7 response
     * @param Request  $request  PSR-7 request
     *
     * @return Response HTTP response
     */
    private function getRedirectResponse(Request $request, Response $response)
    {
        try {
            $videoUrls = $this->video->getUrl();
        } catch (EmptyUrlException $e) {
            /*
            If this happens it is probably a playlist
            so it will either be handled by getStream() or throw an exception anyway.
             */
            $videoUrls = [];
        }
        if (count($videoUrls) > 1) {
            return $this->getRemuxStream($request, $response);
        } elseif ($this->config->stream) {
            return $this->getStream($request, $response);
        } else {
            if (empty($videoUrls[0])) {
                throw new Exception(_("Can't find URL of video."));
            }

            return $response->withRedirect($videoUrls[0]);
        }
    }

    /**
     * Return a converted video file.
     *
     * @param Request  $request  PSR-7 request
     * @param Response $response PSR-7 response
     *
     * @return Response HTTP response
     */
    private function getConvertedResponse(Request $request, Response $response)
    {
        $response = $response->withHeader(
            'Content-Disposition',
            'attachment; filename="'.
            $this->video->getFileNameWithExtension($request->getQueryParam('customFormat')).'"'
        );
        $response = $response->withHeader('Content-Type', 'video/'.$request->getQueryParam('customFormat'));

        if ($request->isGet() || $request->isPost()) {
            $process = $this->video->getConvertedStream(
                $request->getQueryParam('customBitrate'),
                $request->getQueryParam('customFormat')
            );
            $response = $response->withBody(new Stream($process));
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
        $format = $this->getFormat($request);
        $url = $request->getQueryParam('url');

        if (isset($url)) {
            $this->video = new Video($url, $format, $this->sessionSegment->getFlash($url));

            try {
                if ($this->config->convertAdvanced && !is_null($request->getQueryParam('customConvert'))) {
                    return $this->getConvertedResponse($request, $response);
                }

                return $this->getRedirectResponse($request, $response);
            } catch (PasswordException $e) {
                return $response->withRedirect(
                    $this->container->get('router')->pathFor('video').'?url='.urlencode($url)
                );
            } catch (Exception $e) {
                $response->getBody()->write($e->getMessage());

                return $response->withHeader('Content-Type', 'text/plain')->withStatus(500);
            }
        } else {
            return $response->withRedirect($this->container->get('router')->pathFor('index'));
        }
    }

    /**
     * Return the JSON object generated by youtube-dl.
     *
     * @param Request  $request  PSR-7 request
     * @param Response $response PSR-7 response
     *
     * @return Response HTTP response
     */
    public function json(Request $request, Response $response)
    {
        $format = $this->getFormat($request);
        $url = $request->getQueryParam('url');

        if (isset($url)) {
            try {
                $this->video = new Video($url, $format);

                return $response->withJson($this->video->getJson());
            } catch (Exception $e) {
                return $response->withJson(['error' => $e->getMessage()])
                    ->withStatus(500);
            }
        } else {
            return $response->withJson(['error' => 'You need to provide the url parameter'])
                ->withStatus(400);
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

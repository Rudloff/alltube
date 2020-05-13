<?php

/**
 * FrontController class.
 */

namespace Alltube\Controller;

use Alltube\Exception\PasswordException;
use Alltube\Locale;
use Alltube\Video;
use Symfony\Component\ErrorHandler\ErrorHandler;
use Symfony\Component\ErrorHandler\ErrorRenderer\HtmlErrorRenderer;
use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Throwable;
use Exception;
use Psr\Container\ContainerInterface;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Views\Smarty;

/**
 * Main controller.
 */
class FrontController extends BaseController
{
    /**
     * Smarty view.
     *
     * @var Smarty
     */
    private $view;

    /**
     * BaseController constructor.
     *
     * @param ContainerInterface $container Slim dependency container
     */
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->view = $this->container->get('view');
    }

    /**
     * Display index page.
     *
     * @param Request $request PSR-7 request
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
                'config' => $this->config,
                'class' => 'index',
                'description' => $this->localeManager->t(
                    'Easily download videos from Youtube, Dailymotion, Vimeo and other websites.'
                ),
                'domain' => $uri->getScheme() . '://' . $uri->getAuthority(),
                'canonical' => $this->getCanonicalUrl($request),
                'supportedLocales' => $this->localeManager->getSupportedLocales(),
                'locale' => $this->localeManager->getLocale(),
            ]
        );

        return $response;
    }

    /**
     * Switch locale.
     *
     * @param Request $request PSR-7 request
     * @param Response $response PSR-7 response
     * @param string[] $data Query parameters
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
     * @param Request $request PSR-7 request
     * @param Response $response PSR-7 response
     *
     * @return Response HTTP response
     * @throws PasswordException
     */
    public function extractors(Request $request, Response $response)
    {
        $this->view->render(
            $response,
            'extractors.tpl',
            [
                'config' => $this->config,
                'extractors' => Video::getExtractors(),
                'class' => 'extractors',
                'title' => $this->localeManager->t('Supported websites'),
                'description' => $this->localeManager->t('List of all supported websites from which Alltube Download ' .
                    'can extract video or audio files'),
                'canonical' => $this->getCanonicalUrl($request),
                'locale' => $this->localeManager->getLocale(),
            ]
        );

        return $response;
    }

    /**
     * Display a password prompt.
     *
     * @param Request $request PSR-7 request
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
                'config' => $this->config,
                'class' => 'password',
                'title' => $this->localeManager->t('Password prompt'),
                'description' => $this->localeManager->t(
                    'You need a password in order to download this video with Alltube Download'
                ),
                'canonical' => $this->getCanonicalUrl($request),
                'locale' => $this->localeManager->getLocale(),
            ]
        );

        return $response;
    }

    /**
     * Return the video description page.
     *
     * @param Request $request PSR-7 request
     * @param Response $response PSR-7 response
     *
     * @return Response HTTP response
     */
    private function getInfoResponse(Request $request, Response $response)
    {
        try {
            $this->video->getJson();
        } catch (PasswordException $e) {
            return $this->password($request, $response);
        }

        if (isset($this->video->entries)) {
            $template = 'playlist.tpl';
        } else {
            $template = 'info.tpl';
        }
        $title = $this->localeManager->t('Video download');
        $description = $this->localeManager->t(
            'Download video from @extractor',
            ['@extractor' => $this->video->extractor_key]
        );
        if (isset($this->video->title)) {
            $title = $this->video->title;
            $description = $this->localeManager->t(
                'Download @title from @extractor',
                [
                    '@title' => $this->video->title,
                    '@extractor' => $this->video->extractor_key
                ]
            );
        }
        $this->view->render(
            $response,
            $template,
            [
                'video' => $this->video,
                'class' => 'info',
                'title' => $title,
                'description' => $description,
                'config' => $this->config,
                'canonical' => $this->getCanonicalUrl($request),
                'locale' => $this->localeManager->getLocale(),
                'defaultFormat' => $this->defaultFormat,
            ]
        );

        return $response;
    }

    /**
     * Dislay information about the video.
     *
     * @param Request $request PSR-7 request
     * @param Response $response PSR-7 response
     *
     * @return Response HTTP response
     */
    public function info(Request $request, Response $response)
    {
        $url = $request->getQueryParam('url') ?: $request->getQueryParam('v');

        if (isset($url) && !empty($url)) {
            $this->video = new Video($url, $this->getFormat($request), $this->getPassword($request));

            if ($this->config->convert && $request->getQueryParam('audio')) {
                // We skip the info page and get directly to the download.
                return $response->withRedirect(
                    $this->container->get('router')->pathFor('download') .
                    '?' . http_build_query($request->getQueryParams())
                );
            } else {
                return $this->getInfoResponse($request, $response);
            }
        } else {
            return $response->withRedirect($this->container->get('router')->pathFor('index'));
        }
    }

    /**
     * Display an error page.
     *
     * @param Request $request PSR-7 request
     * @param Response $response PSR-7 response
     * @param Throwable $error Error to display
     *
     * @return Response HTTP response
     */
    public function error(Request $request, Response $response, Throwable $error)
    {
        if ($this->config->debug) {
            $renderer = new HtmlErrorRenderer(true);
            $exception = $renderer->render($error);
            $response->getBody()->write($exception->getAsString());

            return $response->withStatus($exception->getStatusCode());
        } else {
            if ($error instanceof Exception) {
                $message = $error->getMessage();
            } else {
                $message = '';
            }

            $this->view->render(
                $response,
                'error.tpl',
                [
                    'config' => $this->config,
                    'error' => $message,
                    'class' => 'video',
                    'title' => $this->localeManager->t('Error'),
                    'canonical' => $this->getCanonicalUrl($request),
                    'locale' => $this->localeManager->getLocale(),
                ]
            );

            return $response->withStatus(500);
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
            $return .= '?' . $query;
        }

        return $return;
    }
}

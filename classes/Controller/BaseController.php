<?php

/**
 * BaseController class.
 */

namespace Alltube\Controller;

use Alltube\Config;
use Alltube\Library\Downloader;
use Alltube\Library\Video;
use Alltube\LocaleManager;
use Aura\Session\Segment;
use Graby\HttpClient\Plugin\ServerSideRequestForgeryProtection\Exception\InvalidURLException;
use Graby\HttpClient\Plugin\ServerSideRequestForgeryProtection\Options;
use Graby\HttpClient\Plugin\ServerSideRequestForgeryProtection\Url;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Router;

/**
 * Abstract class used by every controller.
 */
abstract class BaseController
{
    /**
     * Current video.
     *
     * @var Video
     */
    protected $video;

    /**
     * Default youtube-dl format.
     *
     * @var string
     */
    protected $defaultFormat = 'best/bestvideo';

    /**
     * Slim dependency container.
     *
     * @var ContainerInterface
     */
    protected $container;

    /**
     * Config instance.
     *
     * @var Config
     */
    protected $config;

    /**
     * Session segment used to store session variables.
     *
     * @var Segment
     */
    protected $sessionSegment;

    /**
     * LocaleManager instance.
     *
     * @var LocaleManager
     */
    protected $localeManager;

    /**
     * Downloader instance.
     *
     * @var Downloader
     */
    protected $downloader;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var Router
     */
    protected $router;

    /**
     * BaseController constructor.
     *
     * @param ContainerInterface $container Slim dependency container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->config = $container->get('config');
        $this->container = $container;
        $session = $container->get('session');
        $this->sessionSegment = $session->getSegment(self::class);
        $this->localeManager = $this->container->get('locale');
        $this->downloader = $this->config->getDownloader();
        $this->router = $this->container->get('router');
        $this->logger = $this->container->get('logger');
        $this->downloader->setLogger($this->logger);

        if (!$this->config->stream) {
            // Force HTTP if stream is not enabled.
            $this->defaultFormat = Config::addHttpToFormat($this->defaultFormat);
        }
    }

    /**
     * Get video format from request parameters or default format if none is specified.
     *
     * @param Request $request PSR-7 request
     *
     * @return string format
     */
    protected function getFormat(Request $request): string
    {
        $format = $request->getQueryParam('format');
        if (!isset($format)) {
            $format = $this->defaultFormat;
        }

        return $format;
    }

    /**
     * Get the password entered for the current video.
     *
     * @param Request $request PSR-7 request
     *
     * @return string|null Password
     * @throws InvalidURLException
     */
    protected function getPassword(Request $request): ?string
    {
        $url = $this->getVideoPageUrl($request);

        $password = $request->getParam('password');
        if (isset($password)) {
            $this->sessionSegment->setFlash($url, $password);
        } else {
            $password = $this->sessionSegment->getFlash($url);
        }

        return $password;
    }

    /**
     * Display an user-friendly error.
     *
     * @param Request $request PSR-7 request
     * @param Response $response PSR-7 response
     * @param string $message Error message
     *
     * @return Response HTTP response
     */
    protected function displayError(Request $request, Response $response, string $message): Response
    {
        $controller = new FrontController($this->container);

        return $controller->displayError($request, $response, $message);
    }

    /**
     * @param Request $request
     * @return string
     * @throws InvalidURLException
     */
    protected function getVideoPageUrl(Request $request): string
    {
        // Prevent SSRF attacks.
        $parts = Url::validateUrl($request->getQueryParam('url'), new Options());

        return $parts['url'];
    }
}

<?php

/**
 * BaseController class.
 */

namespace Alltube\Controller;

use Alltube\Config;
use Alltube\LocaleManager;
use Alltube\SessionManager;
use Alltube\Video;
use Aura\Session\Segment;
use Psr\Container\ContainerInterface;
use Slim\Http\Request;

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
    protected $defaultFormat = 'best[protocol=https]/best[protocol=http]';

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
     * BaseController constructor.
     *
     * @param ContainerInterface $container Slim dependency container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->config = Config::getInstance();
        $this->container = $container;
        $session = SessionManager::getSession();
        $this->sessionSegment = $session->getSegment(self::class);
        $this->localeManager = $this->container->get('locale');

        if ($this->config->stream) {
            $this->defaultFormat = 'best';
        }
    }

    /**
     * Get video format from request parameters or default format if none is specified.
     *
     * @param Request $request PSR-7 request
     *
     * @return string format
     */
    protected function getFormat(Request $request)
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
     * @return string Password
     */
    protected function getPassword(Request $request)
    {
        $url = $request->getQueryParam('url');

        $password = $request->getParam('password');
        if (isset($password)) {
            $this->sessionSegment->setFlash($url, $password);
        } else {
            $password = $this->sessionSegment->getFlash($url);
        }

        return $password;
    }
}

<?php

namespace Alltube;

use Alltube\Controller\DownloadController;
use Alltube\Controller\FrontController;
use Alltube\Controller\JsonController;
use Alltube\Exception\ConfigException;
use Alltube\Exception\DependencyException;
use Alltube\Factory\ConfigFactory;
use Alltube\Factory\LocaleManagerFactory;
use Alltube\Factory\LoggerFactory;
use Alltube\Factory\SessionFactory;
use Alltube\Factory\ViewFactory;
use Alltube\Middleware\CspMiddleware;
use Alltube\Middleware\LinkHeaderMiddleware;
use Alltube\Middleware\LocaleMiddleware;
use Alltube\Middleware\RouterPathMiddleware;
use Slim\Container;
use SmartyException;

class App extends \Slim\App
{
    /**
     * App constructor.
     * @throws ConfigException
     * @throws DependencyException
     * @throws SmartyException
     */
    public function __construct()
    {
        parent::__construct();

        /** @var Container $container */
        $container = $this->getContainer();

        // Config.
        $container['config'] = ConfigFactory::create($container);

        // Session.
        $container['session'] = SessionFactory::create($container);

        // Locales.
        $container['locale'] = LocaleManagerFactory::create($container);

        // Smarty.
        $container['view'] = ViewFactory::create($container);

        // Logger.
        $container['logger'] = LoggerFactory::create($container);

        // Middlewares.
        $this->add(new LocaleMiddleware($container));
        $this->add(new CspMiddleware($container));
        $this->add(new LinkHeaderMiddleware($container));
        $this->add(new RouterPathMiddleware($container));

        // Controllers.
        $frontController = new FrontController($container);
        $jsonController = new JsonController($container);
        $downloadController = new DownloadController($container);

        // Error handling.
        $container['errorHandler'] = [$frontController, 'error'];
        $container['phpErrorHandler'] = [$frontController, 'error'];
        $container['notFoundHandler'] = [$frontController, 'notFound'];
        $container['notAllowedHandler'] = [$frontController, 'notAllowed'];

        // Routes.
        $this->get(
            '/',
            [$frontController, 'index']
        )->setName('index');

        $this->get(
            '/extractors',
            [$frontController, 'extractors']
        )->setName('extractors');

        $this->any(
            '/info',
            [$frontController, 'info']
        )->setName('info');

        $this->any(
            '/watch',
            [$frontController, 'info']
        );

        $this->any(
            '/download',
            [$downloadController, 'download']
        )->setName('download');

        $this->get(
            '/locale/{locale}',
            [$frontController, 'locale']
        )->setName('locale');

        $this->get(
            '/json',
            [$jsonController, 'json']
        )->setName('json');
    }
}

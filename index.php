<?php

require_once __DIR__ . '/vendor/autoload.php';

use Alltube\Controller\DownloadController;
use Alltube\Controller\FrontController;
use Alltube\Controller\JsonController;
use Alltube\ErrorHandler;
use Alltube\Factory\ConfigFactory;
use Alltube\Factory\LocaleManagerFactory;
use Alltube\Factory\LoggerFactory;
use Alltube\Factory\SessionFactory;
use Alltube\Factory\ViewFactory;
use Alltube\Middleware\CspMiddleware;
use Alltube\Middleware\LinkHeaderMiddleware;
use Alltube\Middleware\LocaleMiddleware;
use Alltube\Middleware\RouterPathMiddleware;
use Slim\App;
use Slim\Container;

if (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/index.php') !== false) {
    header('Location: ' . str_ireplace('/index.php', '/', $_SERVER['REQUEST_URI']));
    die;
}

try {
    // Create app.
    $app = new App();

    /** @var Container $container */
    $container = $app->getContainer();

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
    $app->add(new LocaleMiddleware($container));
    $app->add(new CspMiddleware($container));
    $app->add(new LinkHeaderMiddleware($container));
    $app->add(new RouterPathMiddleware($container));

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
    $app->get(
        '/',
        [$frontController, 'index']
    )->setName('index');

    $app->get(
        '/extractors',
        [$frontController, 'extractors']
    )->setName('extractors');

    $app->any(
        '/info',
        [$frontController, 'info']
    )->setName('info');

    $app->any(
        '/watch',
        [$frontController, 'info']
    );

    $app->any(
        '/download',
        [$downloadController, 'download']
    )->setName('download');

    $app->get(
        '/locale/{locale}',
        [$frontController, 'locale']
    )->setName('locale');

    $app->get(
        '/json',
        [$jsonController, 'json']
    )->setName('json');

    $app->run();
} catch (Throwable $e) {
    ErrorHandler::handle($e);
}

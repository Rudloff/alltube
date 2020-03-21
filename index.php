<?php

require_once __DIR__ . '/vendor/autoload.php';
use Alltube\Config;
use Alltube\Controller\DownloadController;
use Alltube\Controller\FrontController;
use Alltube\Controller\JsonController;
use Alltube\LocaleManager;
use Alltube\LocaleMiddleware;
use Alltube\UglyRouter;
use Alltube\ViewFactory;
use Slim\App;
use Symfony\Component\Debug\Debug;

if (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/index.php') !== false) {
    header('Location: ' . str_ireplace('/index.php', '/', $_SERVER['REQUEST_URI']));
    die;
}

if (is_file(__DIR__ . '/config/config.yml')) {
    Config::setFile(__DIR__ . '/config/config.yml');
}

// Create app.
$app = new App();
$container = $app->getContainer();

// Load config.
$config = Config::getInstance();
if ($config->uglyUrls) {
    $container['router'] = new UglyRouter();
}
if ($config->debug) {
    /*
     We want to enable this as soon as possible,
     in order to catch errors that are thrown
     before the Slim error handler is ready.
     */
    Debug::enable();
}

// Locales.
if (!class_exists('Locale')) {
    die('You need to install the intl extension for PHP.');
}
$container['locale'] = LocaleManager::getInstance();
$app->add(new LocaleMiddleware($container));

// Smarty.
$container['view'] = ViewFactory::create($container);

// Controllers.
$frontController = new FrontController($container);
$jsonController = new JsonController($container);
$downloadController = new DownloadController($container);

// Error handling.
$container['errorHandler'] = [$frontController, 'error'];
$container['phpErrorHandler'] = [$frontController, 'error'];

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
// Legacy route.
$app->any('/video', [$frontController, 'info']);

$app->any(
    '/watch',
    [$frontController, 'info']
);

$app->any(
    '/download',
    [$downloadController, 'download']
)->setName('download');
// Legacy route.
$app->get('/redirect', [$downloadController, 'download']);

$app->get(
    '/locale/{locale}',
    [$frontController, 'locale']
)->setName('locale');

$app->get(
    '/json',
    [$jsonController, 'json']
)->setName('json');

try {
    $app->run();
} catch (SmartyException $e) {
    die('Smarty could not compile the template file: ' . $e->getMessage());
}

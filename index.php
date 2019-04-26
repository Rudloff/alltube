<?php

require_once __DIR__.'/vendor/autoload.php';
use Alltube\Config;
use Alltube\Controller\DownloadController;
use Alltube\Controller\FrontController;
use Alltube\Controller\JsonController;
use Alltube\LocaleManager;
use Alltube\LocaleMiddleware;
use Alltube\UglyRouter;
use Alltube\ViewFactory;
use Slim\App;

if (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/index.php') !== false) {
    header('Location: '.str_ireplace('/index.php', '/', $_SERVER['REQUEST_URI']));
    die;
}

if (is_file(__DIR__.'/config/config.yml')) {
    Config::setFile(__DIR__.'/config/config.yml');
}

$app = new App();
$container = $app->getContainer();
$config = Config::getInstance();
if ($config->uglyUrls) {
    $container['router'] = new UglyRouter();
}
$container['view'] = ViewFactory::create($container);

if (!class_exists('Locale')) {
    die('You need to install the intl extension for PHP.');
}
$container['locale'] = new LocaleManager();
$app->add(new LocaleMiddleware($container));

$frontController = new FrontController($container);
$jsonController = new JsonController($container);
$downloadController = new DownloadController($container);

$container['errorHandler'] = [$frontController, 'error'];

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
    [$frontController, 'video']
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
    die('Smarty could not compile the template file: '.$e->getMessage());
}

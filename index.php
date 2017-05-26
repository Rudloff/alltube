<?php


$language = "zh_CN";
putenv("LANG=".$language);
setlocale(LC_ALL, [$language, $language.'.utf8']);

require_once __DIR__.'/vendor/autoload.php';
use Alltube\Config;
use Alltube\Controller\FrontController;
use Alltube\UglyRouter;
use Alltube\ViewFactory;
use Slim\App;

if (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/index.php') !== false) {
    header('Location: '.str_ireplace('/index.php', '/', $_SERVER['REQUEST_URI']));
    die;
}

$app = new App();
$container = $app->getContainer();
$config = Config::getInstance();
if ($config->uglyUrls) {
    $container['router'] = new UglyRouter();
}
$container['view'] = ViewFactory::create($container);

$controller = new FrontController($container, null, $_COOKIE);

$container['errorHandler'] = [$controller, 'error'];

$app->get(
    '/',
    [$controller, 'index']
)->setName('index');
$app->get(
    '/extractors',
    [$controller, 'extractors']
)->setName('extractors');
$app->any(
    '/video',
    [$controller, 'video']
)->setName('video');
$app->get(
    '/redirect',
    [$controller, 'redirect']
)->setName('redirect');
$app->run();

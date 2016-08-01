<?php
require_once __DIR__.'/vendor/autoload.php';
use Alltube\VideoDownload;
use Alltube\Controller\FrontController;

if (strpos($_SERVER['REQUEST_URI'], '/index.php') !== false) {
    header('Location: '.str_ireplace('/index.php', '/', $_SERVER['REQUEST_URI']));
    die;
}

$app = new \Slim\App();
$container = $app->getContainer();
$container['view'] = function ($c) {
    $view = new \Slim\Views\Smarty(__DIR__.'/templates/');

    $view->addSlimPlugins($c['router'], $c['request']->getUri());
    $view->registerPlugin('modifier', 'noscheme', 'Smarty_Modifier_noscheme');


    return $view;
};

$controller = new FrontController($container);

$container['errorHandler'] = array($controller, 'error');

$app->get(
    '/',
    array($controller, 'index')
)->setName('index');
$app->get(
    '/extractors',
    array($controller, 'extractors')
)->setName('extractors');
$app->get(
    '/video',
    array($controller, 'video')
)->setName('video');
$app->get(
    '/redirect',
    array($controller, 'redirect')
)->setName('redirect');
$app->get(
    '/json',
    array($controller, 'json')
);
$app->run();

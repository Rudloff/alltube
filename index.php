<?php

require_once __DIR__.'/vendor/autoload.php';
use Alltube\Controller\FrontController;

if (strpos($_SERVER['REQUEST_URI'], '/index.php') !== false) {
    header('Location: '.str_ireplace('/index.php', '/', $_SERVER['REQUEST_URI']));
    die;
}

$app = new \Slim\App();
$container = $app->getContainer();
$container['view'] = function ($c) {
    $view = new \Slim\Views\Smarty(__DIR__.'/templates/');

    $smartyPlugins = new \Slim\Views\SmartyPlugins($c['router'], $c['request']->getUri());
    $view->registerPlugin('function', 'path_for', [$smartyPlugins, 'pathFor']);
    $view->registerPlugin('function', 'base_url', [$smartyPlugins, 'baseUrl']);

    $view->registerPlugin('modifier', 'noscheme', 'Smarty_Modifier_noscheme');


    return $view;
};

$controller = new FrontController($container);

$container['errorHandler'] = [$controller, 'error'];

$app->get(
    '/',
    [$controller, 'index']
)->setName('index');
$app->get(
    '/extractors',
    [$controller, 'extractors']
)->setName('extractors');
$app->get(
    '/video',
    [$controller, 'video']
)->setName('video');
$app->get(
    '/redirect',
    [$controller, 'redirect']
)->setName('redirect');
$app->get(
    '/json',
    [$controller, 'json']
);
$app->run();

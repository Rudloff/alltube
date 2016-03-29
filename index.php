<?php
/**
 * PHP web interface for youtube-dl (http://rg3.github.com/youtube-dl/)
 * Index page
 *
 * PHP Version 5.3.10
 *
 * @category Youtube-dl
 * @package  Youtubedl
 * @author   Pierre Rudloff <contact@rudloff.pro>
 * @author   Olivier Haquette <contact@olivierhaquette.fr>
 * @license  GNU General Public License http://www.gnu.org/licenses/gpl.html
 * @link     http://rudloff.pro
 * */
require_once __DIR__.'/vendor/autoload.php';
use Alltube\VideoDownload;

$app = new \Slim\App();
$container = $app->getContainer();
$container['view'] = function ($c) {
    $view = new \Slim\Views\Smarty(__DIR__.'/templates/');

    $view->addSlimPlugins($c['router'], $c['request']->getUri());

    return $view;
};

$app->get(
    '/',
    array('Alltube\Controller\FrontController', 'index')
);
$app->get(
    '/extractors',
    array('Alltube\Controller\FrontController', 'extractors')
)->setName('extractors');
$app->get(
    '/video',
    array('Alltube\Controller\FrontController', 'video')
)->setName('video');
$app->get(
    '/redirect',
    array('Alltube\Controller\FrontController', 'redirect')
);
$app->get(
    '/json',
    array('Alltube\Controller\FrontController', 'json')
);
$app->run();

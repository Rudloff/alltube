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
use Alltube\Controller\FrontController;

$app = new \Slim\App();
$container = $app->getContainer();
$container['view'] = function ($c) {
    $view = new \Slim\Views\Smarty(__DIR__.'/templates/');

    $view->addSlimPlugins($c['router'], $c['request']->getUri());
    $view->registerPlugin('modifier', 'noscheme', 'Smarty_Modifier_noscheme');


    return $view;
};

$controller = new FrontController();

$app->get(
    '/',
    array($controller, 'index')
);
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

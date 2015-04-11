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
require_once 'common.php';
$smarty->assign('class', 'index');
$smarty->display('head.tpl');
$smarty->display('header.tpl');
$smarty->display('index.tpl');
$smarty->display('footer.tpl');

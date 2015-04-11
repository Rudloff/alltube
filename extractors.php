<?php
/**
 * PHP web interface for youtube-dl (http://rg3.github.com/youtube-dl/)
 * List of all supported websites
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
$smarty->assign('class', 'extractors');
require_once 'download.php';
$smarty->display('head.tpl');
$smarty->display('header.tpl');
$smarty->display('logo.tpl');
$smarty->assign('extractors', VideoDownload::listExtractors());
$smarty->display('extractors.tpl');
$smarty->display('footer.tpl');

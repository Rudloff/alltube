<?php
/**
 * PHP web interface for youtube-dl (http://rg3.github.com/youtube-dl/)
 * PHP file included on all pages
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
require_once 'vendor/autoload.php';
if (is_file('config.php')) {
    include_once 'config.php';
} else {
    include_once 'config.example.php';
}
define('FILENAME', basename($_SERVER["SCRIPT_FILENAME"]));
if (DISABLED && FILENAME != 'disabled.php') {
    header('Location: disabled.php'); exit;
} else if (MAINTENANCE && FILENAME != 'maintenance.php') {
    header('Location: maintenance.php'); exit;
}
$smarty = new Smarty();
$smarty->assign(
    array(
        'base_url'=>BASE_URL,
        'convert'=>CONVERT
    )
);

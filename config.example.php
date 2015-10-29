<?php
/**
 * PHP web interface for youtube-dl (http://rg3.github.com/youtube-dl/)
 * Config file
 *
 * PHP Version 5.3.10
 *
 * @category Youtube-dl
 * @package  Youtubedl
 * @author   Pierre Rudloff <contact@rudloff.pro>
 * @license  GNU General Public License http://www.gnu.org/licenses/gpl.html
 * @link     http://rudloff.pro
 * */
define('YOUTUBE_DL', __DIR__.'/vendor/rg3/youtube-dl/youtube_dl/__main__.py');
define('PYTHON', '/usr/bin/python');
define('PARAMS', '--no-playlist --no-warnings -f best');
if (getenv('CONVERT')) {
    define('CONVERT', getenv('CONVERT'));
} else {
    define('CONVERT', false);
}
define('AVCONV', __DIR__.'/ffmpeg/ffmpeg');
define('MAINTENANCE', false);
define('DISABLED', false);

<?php
/**
 * PHP web interface for youtube-dl (http://rg3.github.com/youtube-dl/)
 * Header
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
?>
<header>
    <div class="social">
        <a class="twitter" href="http://twitter.com/home?status=<?php
        echo urlencode('http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']);
        ?>" target="_blank">
            Share on Twitter<div class="twittermask"></div></a>
        <a class="facebook" href="https://www.facebook.com/sharer/sharer.php?u=<?php
        echo urlencode('http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']);
        ?>" target="_blank">Share on Facebook<div class="facebookmask"></div></a>
    </div>
    </header>

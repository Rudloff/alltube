<?php
/**
 * Footer
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
$baseURL = 'http://'.$_SERVER['SERVER_NAME'].dirname($_SERVER['REQUEST_URI']);
?>
<footer>
    <div class="footer_wrapper">
     Code by <a rel="author" target="blank" itemprop="author"
        href="http://rudloff.pro/">Pierre Rudloff</a>
     &middot; Design by
     <a rel="author" itemprop="author" target="blank"
        href="http://olivierhaquette.fr">Olivier Haquette</a>
     &middot;
     <a target="_blank"
        href="https://www.facebook.com/pages/AllTube-Download/571380966249415"
        title="AllTube Download on Facebook">Like us on Facebook</a>
     &middot;
     <a href="https://github.com/Rudloff/alltube">Get the code</a>
     &middot;
     Based on <a href="http://rg3.github.io/youtube-dl/">youtube-dl</a>
     &middot;
     <a href="javascript:window.location='<?php echo $baseURL; ?>/api.php?url='+location.href;">Bookmarklet</a>
     </div>
</footer>

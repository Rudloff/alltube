<?php
/**
 * PHP web interface for youtube-dl (http://rg3.github.com/youtube-dl/)
 * Index page
 * 
 * PHP Version 5.3.10
 * 
 * @category Youtube-dl
 * @package  Youtubedl
 * @author   Pierre Rudloff <rudloff@strasweb.fr>
 * @author   Olivier Haquette <contact@olivierhaquette.fr>
 * @license  GNU General Public License http://www.gnu.org/licenses/gpl.html
 * @link     http://rudloff.pro
 * */
require_once 'config.php';
if (!MAINTENANCE) {
    header('Location: index.php'); exit;
}
require 'head.php';
?>

<body>

<?php
    require 'header.php';
?>
    
<div class="wrapper">
    <div class="main">
    <h1><img itemprop="image" class="logo" src="img/logo.png"
    alt="AllTube Download" width="328" height="284"></h1>
    <div>Due to some issues with our server,
        we have to disable AllTube for a few days.
        Sorry for the inconvenience.</div>
</div>
</div>
        
    <?php
        require 'footer.php';
    ?>
    
</body>

</html>

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
require_once 'config.php';
if (!DISABLED) {
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
    <div>
        Due to various technical reasons,
            we can no longer host an online version of AllTube.<br/>
        However, you are free to
            <a title="AllTube releases on GitHub"
                href="https://github.com/Rudloff/alltube/releases">
                download the code</a>
            and run it on your own server.
    </div>
</div>
</div>
        
    <?php
        require 'footer.php';
    ?>
    
</body>

</html>

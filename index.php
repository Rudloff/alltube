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
require_once 'config.example.php';
@include_once 'config.php';
require_once 'common.php';
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
    <form action="api.php">
    <label class="labelurl" for="url">
        Copy here the URL of your video (Youtube, Dailymotion, etc.)
    </label>
    <div class="champs">
        <span class="URLinput_wrapper">
        <input class="URLinput" type="url" name="url" id="url"
        required placeholder="http://website.com/video" />
        </span>
        <input class="downloadBtn" type="submit" value="Download" /><br/>
        <?php
        if (CONVERT) {
            ?>
            <div class="mp3">
                <p><input type="checkbox" id="audio" class="audio" name="audio">
                <label for="audio"><span class="ui"></span>
                    Audio only (MP3)</label></p>
            </div>
            <?php
        }
        ?>
    </div>
    </form>
    <a class="combatiblelink" href="extractors.php">See all supported websites</a>
</div>
</div>
        
    <?php
        require 'footer.php';
    ?>
    
</body>

</html>

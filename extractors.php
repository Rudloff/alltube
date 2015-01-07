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
require_once 'config.php';
require_once 'common.php';
require 'head.php';
?>

<body class="extractors">

<?php
    require 'header.php';
?>
    
    <div class="wrapper">


    <?php
    require 'logo.php';
    ?>


    <h2 class="titre">Supported websites</h2>
    
    <div class="tripleliste">
    
    
            <ul>
            <?php
            require_once 'download.php';
            $extractors=(VideoDownload::listExtractors());
            foreach ($extractors as $extractor) {
                echo '<li>'.$extractor.'</li>';
            }
            ?>
            </ul>
    </div>
    </div>

    <?php
        require 'footer.php';
    ?>

</body>

</html>

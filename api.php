<?php
/**
 * PHP web interface for youtube-dl (http://rg3.github.com/youtube-dl/)
 *
 * PHP Version 5.3.10
 *
 * @category Youtube-dl
 * @package  Youtubedl
 * @author   Pierre Rudloff <rudloff@strasweb.fr>
 * @license  GNU General Public License http://www.gnu.org/licenses/gpl.html
 * @link     http://rudloff.pro
 * */
$python="/usr/bin/python";
require_once 'download.php';
if (isset($_GET["url"])) {
    if (isset($_GET["format"])) {
        $video = VideoDownload::getJSON($_GET["url"], $_GET["format"]);

        if (isset($video->url)) {
            header("Location: ".$video->url);
            exit;
        } else {
            $error=true;
        }
    } else {
        $video = VideoDownload::getJSON($_GET["url"]);
        if (isset($video->webpage_url)) {
            include 'head.php';
            ?>
            <body>
                <div itemscope itemtype="http://schema.org/VideoObject" class="wrapper">
            <div class="main">
                <?php
                include 'logo.php';
                ?>
            <p>You are going to download<i itemprop="name">
                <a itemprop="url" id="video_link" data-ext="<?php echo $video->ext; ?>" data-video="<?php echo $video->url; ?>" href="<?php echo $video->webpage_url; ?>">
            <?php
            echo $video->title;
            ?></a></i>. <img class="cast_icon" id="cast_disabled" src="img/ic_media_route_disabled_holo_light.png" alt="Google Cast™ is disabled" title="Google Cast is not supported on this browser." /><img class="cast_btn cast_hidden cast_icon" id="cast_btn_launch" src="img/ic_media_route_off_holo_light.png" title="Cast to ChromeCast" alt="Google Cast™" /><img src="img/ic_media_route_on_holo_light.png" alt="Casting to ChromeCast…" title="Stop casting" id="cast_btn_stop" class="cast_btn cast_hidden cast_icon" /></p>
            <?php
            echo '<img itemprop="image" class="thumb" src="',
                $video->thumbnail, '" alt="" />';
            ?><br/>
            <form action="api.php">
            <input type="hidden" name="url"
            value="<?php echo $video->webpage_url; ?>" />
            <?php
            if (isset($video->formats)) {
                ?>
                <h3>Available formats:</h3>
                <p>(You might have to do a <i>Right click > Save as</i>)</p>
                <ul id="format" class="format">
                <?php
                foreach ($video->formats as $format) {
                    echo '<li itemprop="encoding" itemscope itemtype="http://schema.org/VideoObject">';
                    echo '<a itemprop="contentUrl" href="', $format->url ,'">';
                    echo '<span itemprop="videoQuality">', $format->format, '</span> (<span itemprop="encodingFormat">',  $format->ext, '</span>)';
                    echo '</a></li>';
                }
                ?>
                </ul><br/><br/>
            <?php
            } else {
                ?>
                <input type="hidden" name="format" value="best" />
            <?php
            }
            ?>
            <input class="downloadBtn" type="submit" value="Download" /><br/>
            </form>
            </div>
            </div>
            <?php
            include 'footer.php';
            ?>
            </body>
            </html>
            <?php
        } else {
            $error=true;
        }
    }
}
if (isset($error)) {
    include 'head.php';
    ?>
    <body>
        <div class="wrapper">
    <div class="main error">
        <?php
        include 'logo.php';
        ?>
    <h2>An error occured</h2>
    Please check the URL of your video.
    If you think this is a bug, please report the following error
    to <a href="mailto:contact@rudloff.pro"><i>contact@rudloff.pro</i></a>:
    <p><i>
    <?php
    foreach ($video['error'] as $error) {
        print $error;
        ?>
        <br/>
        <?php
    }
    ?>
    </i></p>
    </div>
    </div>
    <?php
    include 'footer.php';
    ?>
    </body>
    </html>
    <?php
}
?>

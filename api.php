<?php
/**
 * PHP web interface for youtube-dl (http://rg3.github.com/youtube-dl/)
 *
 * PHP Version 5.3.10
 *
 * @category Youtube-dl
 * @package  Youtubedl
 * @author   Pierre Rudloff <contact@rudloff.pro>
 * @license  GNU General Public License http://www.gnu.org/licenses/gpl.html
 * @link     http://rudloff.pro
 * */
require_once 'config.example.php';
@include_once 'config.php';
require_once 'common.php';
require_once 'download.php';
if (isset($_GET["url"])) {
    if (isset($_GET['audio'])) {
        $video = VideoDownload::getJSON($_GET["url"]);

        if (isset($video->url)) {
            //Vimeo needs a correct user-agent
            $UA = VideoDownload::getUA();
            ini_set(
                'user_agent',
                $UA
            );
            $url_info = parse_url($video->url);
            if ($url_info['scheme'] == 'rtmp') {
                header(
                    'Content-Disposition: attachment; filename="'.
                    html_entity_decode(
                        pathinfo(
                            VideoDownload::getFilename(
                                $video->webpage_url
                            ), PATHINFO_FILENAME
                        ).'.mp3', ENT_COMPAT, 'ISO-8859-1'
                    ).'"'
                );
                header("Content-Type: audio/mpeg");
                passthru(
                    '/usr/bin/rtmpdump -q -r '.escapeshellarg($video->url).
                    '   |  /usr/bin/avconv -v quiet -i - -f mp3 pipe:1'
                );
                exit;
            } else {
                header(
                    'Content-Disposition: attachment; filename="'.
                    html_entity_decode(
                        pathinfo(
                            VideoDownload::getFilename(
                                $video->webpage_url
                            ), PATHINFO_FILENAME
                        ).'.mp3', ENT_COMPAT, 'ISO-8859-1'
                    ).'"'
                );
                header("Content-Type: audio/mpeg");
                passthru(
                    '/usr/bin/wget -q --user-agent='.escapeshellarg($UA).
                    '  -O - '.escapeshellarg($video->url).
                    '   |  /usr/bin/avconv -v quiet -i - -f mp3 pipe:1'
                );
                exit;
            }
        } else {
            $error=true;
        }
    } else {
        $video = VideoDownload::getJSON($_GET["url"]);
        if (isset($video->webpage_url)) {
            include 'head.php';
            ?>
            <body>
                <div itemscope
                    itemtype="http://schema.org/VideoObject" class="wrapper">
            <div class="main">
                <?php
                include 'logo.php';
                ?>
            <p>You are going to download<i itemprop="name">
                <a itemprop="url" id="video_link"
                    data-ext="<?php echo $video->ext; ?>"
                    data-video="<?php echo htmlentities($video->url); ?>"
                    href="<?php echo $video->webpage_url; ?>">
            <?php
            echo $video->title;
            ?></a></i>.
            <img class="cast_icon" id="cast_disabled"
                src="img/ic_media_route_disabled_holo_light.png"
                alt="Google Cast™ is disabled"
                title="Google Cast is not supported on this browser." />
            <img class="cast_btn cast_hidden cast_icon" id="cast_btn_launch"
                src="img/ic_media_route_off_holo_light.png"
                title="Cast to ChromeCast" alt="Google Cast™" />
            <img src="img/ic_media_route_on_holo_light.png"
                alt="Casting to ChromeCast…" title="Stop casting"
                id="cast_btn_stop" class="cast_btn cast_hidden cast_icon" /></p>
            <?php
            echo '<img itemprop="image" class="thumb" src="',
                $video->thumbnail, '" alt="" />';
            ?><br/>
            <input type="hidden" name="url"
            value="<?php echo $video->webpage_url; ?>" />
            <?php
            if (isset($video->formats)) {
                ?>
                <h3>Available formats:</h3>
                <p>(You might have to do a <i>Right click > Save as</i>)</p>
                <ul id="format" class="format">
                <?php
                echo '<li class="best" itemprop="encoding" itemscope
                itemtype="http://schema.org/VideoObject">';
                echo '<a download="'.$video->_filename.'" itemprop="contentUrl"
                    href="', htmlentities($video->url) ,'">';
                echo '<b>Best</b> (<span itemprop="encodingFormat">', 
                    $video->ext, '</span>)';
                echo '</a></li>';
                foreach ($video->formats as $format) {
                    echo '<li itemprop="encoding"
                        itemscope itemtype="http://schema.org/VideoObject">';
                    echo '<a download="'.str_replace(
                        $video->ext, $format->ext, $video->_filename
                    ).'" itemprop="contentUrl"
                        href="', htmlentities($format->url) ,'">';
                    echo '<span itemprop="videoQuality">', $format->format,
                        '</span> (<span itemprop="encodingFormat">', 
                        $format->ext, '</span>)';
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
            if (!isset($video->formats)) {
                ?>
                <a class="downloadBtn"
                    href="<?php echo $video->url; ?>">Download</a><br/>
                <?php
            }
            ?>
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
    <p><i>
    <?php
    foreach ($video['error'] as $error) {
        print htmlspecialchars($error);
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

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
    if (isset($_GET["format"]) || isset($_GET['audio'])) {
        $video = json_decode(VideoDownload::getJSON($_GET["url"], $_GET["format"]));
        
        if (isset($video->url)) {
            //Vimeo needs a correct user-agent
            $UA = VideoDownload::getUA();
            ini_set(
                'user_agent',
                $UA
            );
            $url_info = parse_url($video->url);
            if ($url_info['scheme'] == 'rtmp') {
                if (isset($_GET['audio'])) {
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
                            VideoDownload::getFilename(
                                $video->webpage_url, $video->format_id
                            ), ENT_COMPAT, 'ISO-8859-1'
                        ).'"'
                    );
                    header("Content-Type: application/octet-stream");
                    passthru(
                        '/usr/bin/rtmpdump -q -r '.escapeshellarg($video->url)
                    );
                    exit;
                }
                
            } else {
                if (isset($_GET['audio'])) {
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
                } else if (pathinfo($video->url, PATHINFO_EXTENSION) == 'm3u8') {
                    header(
                        'Content-Disposition: attachment; filename="'.
                        html_entity_decode(
                            pathinfo(
                                VideoDownload::getFilename(
                                    $video->webpage_url
                                ), PATHINFO_FILENAME
                            ).'.mp4', ENT_COMPAT, 'ISO-8859-1'
                        ).'"'
                    );
                    header("Content-Type: video/mp4");
                    passthru(
                        '/usr/bin/avconv -v quiet -i '.
                        escapeshellarg($video->url).' -f h264 pipe:1'
                    );
                    exit;
                } else {
                    $headers = get_headers($video->url, 1);
                    header(
                        'Content-Disposition: attachment; filename="'.
                        html_entity_decode(
                            VideoDownload::getFilename(
                                $video->webpage_url, $video->format_id
                            ), ENT_COMPAT, 'ISO-8859-1'
                        ).'"'
                    );
                    if (is_string($headers['Content-Type'])
                        && isset($headers['Content-Type'])
                    ) {
                        header("Content-Type: ".$headers['Content-Type']);
                    } else {
                        header("Content-Type: application/octet-stream");
                    }
                    if (is_string($headers['Content-Length'])
                        && isset($headers['Content-Length'])
                    ) {
                        header("Content-Length: ".$headers['Content-Length']);
                    }
                    readfile($video->url);
                    exit;
                }
            }
        } else {
            $error=true;
        }
    } else {
        $video = json_decode(VideoDownload::getJSON($_GET["url"]));
        if (isset($video->webpage_url)) {
            include 'head.php';
            ?>
            <body>
                <div class="wrapper">
            <div class="main">
                <?php
                include 'logo.php';
                ?>
            <p>You are going to download<i>
                <a href="<?php echo $video->webpage_url; ?>">
            <?php
            echo $video->title;
            ?></a></i>.</p>
            <?php
            echo '<img class="thumb" src="', 
                $video->thumbnail, '" alt="" />';
            ?><br/>
            <form action="api.php">
            <input type="hidden" name="url"
            value="<?php echo $video->webpage_url; ?>" />
            <?php
            if (isset($video->formats)) {
                ?>
                <legend for="format">Select format</legend>
                <select id="format" name="format">
                <?php
                foreach ($video->formats as $format) {
                    echo '<option value="', $format->format_id, '"';
                    if ($format->format_id == $video->format_id) {
                        echo ' selected ';
                    }
                    echo '>';
                    echo $format->format, ' (',  $format->ext, ')';
                    echo '</option>';
                }
                ?>
                </select><br/><br/>
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

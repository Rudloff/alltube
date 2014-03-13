<?php

$python="/usr/bin/python";
require_once 'download.php';
if (isset($_GET["url"])) {
    header('Content-Type: application/json');
    $video = VideoDownload::getJSON($_GET["url"]);
    echo $video;
}
?>

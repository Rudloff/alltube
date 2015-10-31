<?php
function smarty_modifier_noscheme($url) {
    $info = parse_url($url);
    return '//'.$info['host'].$info['path'];
}

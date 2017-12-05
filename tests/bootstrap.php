<?php
/**
 * File used to bootstrap tests.
 */
use Alltube\PlaylistArchiveStream;

/**
 * Composer autoload.
 */
require_once __DIR__.'/../vendor/autoload.php';

ini_set('session.use_cookies', 0);
session_cache_limiter('');
session_start();

stream_wrapper_register('playlist', PlaylistArchiveStream::class);

<?php
/**
 * File used to bootstrap tests.
 */
use Alltube\PlaylistArchiveStream;

/**
 * Composer autoload.
 */
require_once __DIR__.'/../vendor/autoload.php';

session_start();

stream_wrapper_register('playlist', PlaylistArchiveStream::class);

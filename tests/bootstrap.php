<?php
/**
 * File used to bootstrap tests.
 */
use Alltube\PlaylistArchiveStream;
use phpmock\mockery\PHPMockery;

/**
 * Composer autoload.
 */
require_once __DIR__.'/../vendor/autoload.php';

ini_set('session.use_cookies', 0);
session_cache_limiter('');
session_start();

stream_wrapper_register('playlist', PlaylistArchiveStream::class);

/*
 * @see https://bugs.php.net/bug.php?id=68541
 */
PHPMockery::define('Alltube', 'popen');
PHPMockery::define('Alltube', 'fopen');

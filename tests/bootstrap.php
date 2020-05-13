<?php

/**
 * File used to bootstrap tests.
 */

use phpmock\mockery\PHPMockery;

// Composer autoload.
require_once __DIR__ . '/../vendor/autoload.php';

ini_set('session.use_cookies', '0');
session_cache_limiter('');
session_start();

// See https://bugs.php.net/bug.php?id=68541
PHPMockery::define('Alltube', 'popen');
PHPMockery::define('Alltube', 'fopen');

<?php

require_once __DIR__ . '/vendor/autoload.php';

use Alltube\App;
use Alltube\ErrorHandler;

try {
    // Create app.
    $app = new App();

    $app->run();
} catch (Throwable $e) {
    ErrorHandler::handle($e);
}

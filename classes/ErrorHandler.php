<?php

namespace Alltube;

use Slim\Http\StatusCode;
use Symfony\Component\ErrorHandler\ErrorRenderer\HtmlErrorRenderer;
use Throwable;

/**
 * Class ErrorHandler
 * @package Alltube
 */
class ErrorHandler
{
    /**
     * Last resort if the error has not been caught by the Slim error handler for some reason.
     * @param Throwable $e
     * @return void
     */
    public static function handle(Throwable $e): void
    {
        error_log($e);

        if (class_exists(HtmlErrorRenderer::class)) {
            // If dev dependencies are loaded, we can use symfony/error-handler.
            $renderer = new HtmlErrorRenderer(true, null, null, dirname(__DIR__));
            $exception = $renderer->render($e);

            http_response_code($exception->getStatusCode());
            die($exception->getAsString());
        } else {
            http_response_code(StatusCode::HTTP_INTERNAL_SERVER_ERROR);
            die('Error when starting the app: ' . htmlentities($e->getMessage()));
        }
    }
}

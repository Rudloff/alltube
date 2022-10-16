<?php

/**
 * UglyRouter class.
 */

namespace Alltube;

use InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;
use Slim\Http\Uri;
use Slim\Router;

/**
 * Extend Slim's router class in order to disable URL rewriting.
 */
class UglyRouter extends Router
{
    /**
     * Dispatch router for HTTP request.
     *
     * @param ServerRequestInterface $request The current HTTP request object
     *
     * @return int[]|string[]|array[]
     *
     * @link   https://github.com/nikic/FastRoute/blob/master/src/Dispatcher.php
     */
    public function dispatch(ServerRequestInterface $request): array
    {
        $params = $request->getQueryParams();
        $uri = new Uri('', '');

        if (isset($params['page'])) {
            // Build an URI that the router can understand.
            $uri = $uri->withPath($params['page']);
        }

        return $this->createDispatcher()->dispatch(
            $request->getMethod(),
            (string) $uri
        );
    }

    /**
     * Build the path for a named route including the base path.
     *
     * @param mixed $name Route name
     * @param string[] $data Named argument replacement data
     * @param string[] $queryParams Optional query string parameters
     *
     * @return string
     * @throws InvalidArgumentException If required data not provided
     * @throws RuntimeException         If named route does not exist
     */
    public function pathFor($name, array $data = [], array $queryParams = []): string
    {
        $queryParams['page'] = $this->relativePathFor($name, $data);
        $url = Uri::createFromString($this->relativePathFor($name, $data, $queryParams))->withPath('');

        if ($this->basePath) {
            $url = $url->withBasePath($this->basePath);
        }

        return $url;
    }
}

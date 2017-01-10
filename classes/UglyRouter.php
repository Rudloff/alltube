<?php
/**
 * UglyRouter class.
 */

namespace Alltube;

use Psr\Http\Message\ServerRequestInterface;
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
     * @return array
     *
     * @link   https://github.com/nikic/FastRoute/blob/master/src/Dispatcher.php
     */
    public function dispatch(ServerRequestInterface $request)
    {
        parse_str($request->getUri()->getQuery(), $args);
        $uri = '/';
        if (isset($args['page'])) {
            $uri .= $args['page'];
        }

        return $this->createDispatcher()->dispatch(
            $request->getMethod(),
            $uri
        );
    }

    /**
     * Build the path for a named route including the base path.
     *
     * @param string $name        Route name
     * @param array  $data        Named argument replacement data
     * @param array  $queryParams Optional query string parameters
     *
     * @throws \RuntimeException         If named route does not exist
     * @throws \InvalidArgumentException If required data not provided
     *
     * @return string
     */
    public function pathFor($name, array $data = [], array $queryParams = [])
    {
        $url = str_replace('/', '/?page=', $this->relativePathFor($name, $data, $queryParams));

        if ($this->basePath) {
            $url = $this->basePath.$url;
        }

        return $url;
    }
}

<?php

/**
 * UglyRouterTest class.
 */

namespace Alltube\Test;

use Alltube\Exception\ConfigException;
use Alltube\Exception\DependencyException;
use Alltube\UglyRouter;
use Slim\Http\Environment;
use Slim\Http\Request;
use SmartyException;

/**
 * Unit tests for the UglyRouter class.
 */
class UglyRouterTest extends ContainerTest
{
    /**
     * UglyRouter instance.
     *
     * @var UglyRouter
     */
    private $router;

    /**
     * Prepare tests.
     *
     * @throws ConfigException
     * @throws DependencyException
     * @throws SmartyException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->router = new UglyRouter();
        $this->router->map(['GET'], '/foo', [$this, 'fakeHandler'])->setName('foo');
    }

    /**
     * Empty function that only exists so that our route can have a handler.
     *
     * @return void
     */
    private function fakeHandler()
    {
    }

    /**
     * Test the dispatch() function.
     *
     * @return void
     */
    public function testDispatch()
    {
        $this->assertEquals(
            [1, 'route0', []],
            $this->router->dispatch(
                Request::createFromEnvironment(
                    Environment::mock(
                        [
                            'REQUEST_METHOD' => 'GET',
                            'QUERY_STRING' => 'page=foo',
                        ]
                    )
                )
            )
        );
    }

    /**
     * Test the pathFor() function.
     *
     * @return void
     */
    public function testPathFor()
    {
        $this->assertEquals(
            '/?page=%2Ffoo',
            $this->router->pathFor('foo', [], [])
        );
    }

    /**
     * Test the pathFor() function with a base path.
     *
     * @return void
     */
    public function testPathForWithBasePath()
    {
        $this->router->setBasePath('/bar');
        $this->assertEquals(
            '/bar/?page=%2Ffoo',
            $this->router->pathFor('foo', [], [])
        );
    }
}

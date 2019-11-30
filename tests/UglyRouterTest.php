<?php

/**
 * UglyRouterTest class.
 */

namespace Alltube\Test;

use Alltube\UglyRouter;
use Slim\Http\Environment;
use Slim\Http\Request;

/**
 * Unit tests for the UglyRouter class.
 */
class UglyRouterTest extends BaseTest
{
    /**
     * UglyRouter instance.
     *
     * @var UglyRouter
     */
    private $router;

    /**
     * Prepare tests.
     */
    protected function setUp(): void
    {
        $this->router = new UglyRouter();
        $this->router->map(['GET'], '/foo', 'print')->setName('foo');
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
                            'QUERY_STRING'   => 'page=foo',
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
            '/?page=foo',
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
            '/bar/?page=foo',
            $this->router->pathFor('foo', [], [])
        );
    }
}

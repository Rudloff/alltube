<?php

/**
 * LocaleMiddlewareTest class.
 */

namespace Alltube\Test;

use Alltube\LocaleManager;
use Alltube\LocaleMiddleware;
use Slim\Container;
use Slim\Http\Environment;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Unit tests for the FrontController class.
 */
class LocaleMiddlewareTest extends BaseTest
{
    /**
     * LocaleMiddleware instance.
     *
     * @var LocaleMiddleware
     */
    private $middleware;

    /**
     * Slim dependency container.
     *
     * @var Container
     */
    private $container;

    /**
     * Prepare tests.
     */
    protected function setUp(): void
    {
        $this->container = new Container();
        $this->container['locale'] = LocaleManager::getInstance();
        $this->middleware = new LocaleMiddleware($this->container);
    }

    /**
     * Unset locale cookie after each test.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        $this->container['locale']->unsetLocale();
        LocaleManager::destroyInstance();
    }

    /**
     * Test the testLocale() function.
     *
     * @return void
     * @requires OS Linux
     */
    public function testTestLocale()
    {
        $locale = [
            'language' => 'en',
            'region'   => 'US',
        ];
        $this->assertEquals('en_US', $this->middleware->testLocale($locale));
    }

    /**
     * Test the testLocale() function with an unsupported locale.
     *
     * @return void
     */
    public function testLocaleWithWrongLocale()
    {
        $locale = [
            'language' => 'foo',
            'region'   => 'BAR',
        ];
        $this->assertNull($this->middleware->testLocale($locale));
        $this->assertNull($this->middleware->testLocale([]));
    }

    /**
     * Check that the request contains an Accept-Language header.
     *
     * @param Request $request PSR-7 request
     *
     * @return void
     */
    public function assertHeader(Request $request)
    {
        $header = $request->getHeader('Accept-Language');
        $this->assertEquals('foo-BAR', $header[0]);
    }

    /**
     * Check that the request contains no Accept-Language header.
     *
     * @param Request $request PSR-7 request
     *
     * @return void
     */
    public function assertNoHeader(Request $request)
    {
        $header = $request->getHeader('Accept-Language');
        $this->assertEmpty($header);
    }

    /**
     * Test the __invoke() function.
     *
     * @return void
     */
    public function testInvoke()
    {
        $request = Request::createFromEnvironment(Environment::mock());
        $this->middleware->__invoke(
            $request->withHeader('Accept-Language', 'foo-BAR'),
            new Response(),
            [$this, 'assertHeader']
        );
    }

    /**
     * Test the __invoke() function without the Accept-Language header.
     *
     * @return void
     */
    public function testInvokeWithoutHeader()
    {
        $request = Request::createFromEnvironment(Environment::mock());
        $this->middleware->__invoke(
            $request->withoutHeader('Accept-Language'),
            new Response(),
            [$this, 'assertNoHeader']
        );
    }
}

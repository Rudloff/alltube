<?php

/**
 * LocaleMiddlewareTest class.
 */

namespace Alltube\Test;

use Alltube\Exception\ConfigException;
use Alltube\Exception\DependencyException;
use Alltube\Middleware\LocaleMiddleware;
use Slim\Http\Request;
use Slim\Http\Response;
use SmartyException;

/**
 * Unit tests for the FrontController class.
 */
class LocaleMiddlewareTest extends ContainerTest
{
    /**
     * LocaleMiddleware instance.
     *
     * @var LocaleMiddleware
     */
    private $middleware;

    /**
     * Prepare tests.
     *
     * @throws DependencyException
     * @throws ConfigException
     * @throws SmartyException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->middleware = new LocaleMiddleware($this->container);
    }

    /**
     * Unset locale cookie after each test.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        $this->container->get('locale')->unsetLocale();
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
            'region' => 'US',
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
            'region' => 'BAR',
        ];
        $this->assertNull($this->middleware->testLocale($locale));
        $this->assertNull($this->middleware->testLocale([]));
    }

    /**
     * Check that the request contains an Accept-Language header.
     *
     * @param Request $request PSR-7 request
     * @param Response $response
     *
     * @return Response
     */
    public function assertHeader(Request $request, Response $response): Response
    {
        $header = $request->getHeader('Accept-Language');
        $this->assertEquals('foo-BAR', $header[0]);

        return $response;
    }

    /**
     * Check that the request contains no Accept-Language header.
     *
     * @param Request $request PSR-7 request
     * @param Response $response
     *
     * @return Response
     */
    public function assertNoHeader(Request $request, Response $response): Response
    {
        $header = $request->getHeader('Accept-Language');
        $this->assertEmpty($header);

        return $response;
    }

    /**
     * Test the __invoke() function.
     *
     * @return void
     */
    public function testInvoke()
    {
        $this->middleware->__invoke(
            $this->container->get('request')->withHeader('Accept-Language', 'foo-BAR'),
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
        $this->middleware->__invoke(
            $this->container->get('request')->withoutHeader('Accept-Language'),
            new Response(),
            [$this, 'assertNoHeader']
        );
    }
}

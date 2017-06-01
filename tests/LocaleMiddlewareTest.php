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
class LocaleMiddlewareTest extends \PHPUnit_Framework_TestCase
{
    /**
     * LocaleMiddleware instance.
     *
     * @var LocaleMiddleware
     */
    private $middleware;

    /**
     * Prepare tests.
     */
    protected function setUp()
    {
        $container = new Container();
        $container['locale'] = new LocaleManager();
        $this->middleware = new LocaleMiddleware($container);
    }

    /**
     * Test the testLocale() function.
     *
     * @return void
     */
    public function testTestLocale()
    {
        $locale = [
            'language'=> 'fr',
            'region'  => 'FR',
        ];
        $this->assertEquals('fr_FR', $this->middleware->testLocale($locale));
    }

    /**
     * Test the testLocale() function with an unsupported locale.
     *
     * @return void
     */
    public function testLocaleWithWrongLocale()
    {
        $locale = [
            'language'=> 'foo',
            'region'  => 'BAR',
        ];
        $this->assertNull($this->middleware->testLocale($locale));
        $this->assertNull($this->middleware->testLocale([]));
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
            $request->withHeader('Accept-Language', 'fr-FR'),
            new Response(),
            function () {
            }
        );
    }

    /**
     * Test the __invoke() function withot the Accept-Language header.
     *
     * @return void
     */
    public function testInvokeWithoutHeader()
    {
        $request = Request::createFromEnvironment(Environment::mock());
        $this->middleware->__invoke(
            $request->withoutHeader('Accept-Language'),
            new Response(),
            function () {
            }
        );
    }

    /**
     * Test that the environment is correctly set up.
     *
     * @return void
     */
    public function testEnv()
    {
        $this->markTestIncomplete('We need to find a way to reliably test LC_ALL and LANG values');
    }
}

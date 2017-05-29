<?php
/**
 * LocaleMiddlewareTest class.
 */

namespace Alltube\Test;

use Alltube\LocaleMiddleware;
use Slim\Http\Environment;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Unit tests for the FrontController class.
 */
class LocaleMiddlewareTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Prepare tests.
     */
    protected function setUp()
    {
        $this->middleware = new LocaleMiddleware();
    }

    /**
     * Test the testLocale() function.
     *
     * @return void
     */
    public function testTestLocale()
    {
        $locale = [
            'language'=>'fr',
            'region'=>'FR',
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
            'language'=>'foo',
            'region'=>'BAR'
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
        $this->assertEquals('fr_FR', getenv('LANG'));
        $this->assertEquals('fr_FR', setlocale(LC_ALL, null));
    }
}

<?php
/**
 * ViewFactoryTest class.
 */

namespace Alltube\Test;

use Alltube\ViewFactory;
use PHPUnit\Framework\TestCase;
use Slim\Container;
use Slim\Http\Environment;
use Slim\Http\Request;
use Slim\Views\Smarty;

/**
 * Unit tests for the ViewFactory class.
 */
class ViewFactoryTest extends TestCase
{
    /**
     * Test the create() function.
     *
     * @return void
     */
    public function testCreate()
    {
        $view = ViewFactory::create(new Container());
        $this->assertInstanceOf(Smarty::class, $view);
    }

    /**
     * Test the create() function with a X-Forwarded-Proto header.
     *
     * @return void
     */
    public function testCreateWithXForwardedProto()
    {
        $request = Request::createFromEnvironment(Environment::mock());
        $view = ViewFactory::create(new Container(), $request->withHeader('X-Forwarded-Proto', 'https'));
        $this->assertInstanceOf(Smarty::class, $view);
    }
}

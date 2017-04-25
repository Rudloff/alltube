<?php
/**
 * ViewFactoryTest class.
 */

namespace Alltube\Test;

use Alltube\ViewFactory;
use Slim\Container;
use Slim\Views\Smarty;

/**
 * Unit tests for the ViewFactory class.
 */
class ViewFactoryTest extends \PHPUnit_Framework_TestCase
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
}

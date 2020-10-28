<?php

/**
 * ViewFactoryTest class.
 */

namespace Alltube\Test;

use Alltube\Factory\ViewFactory;
use Slim\Views\Smarty;
use SmartyException;

/**
 * Unit tests for the ViewFactory class.
 */
class ViewFactoryTest extends ContainerTest
{
    /**
     * Test the create() function.
     *
     * @return void
     * @throws SmartyException
     */
    public function testCreate()
    {
        $view = ViewFactory::create($this->container);
        $this->assertInstanceOf(Smarty::class, $view);
    }

    /**
     * Test the create() function with a X-Forwarded-Proto header.
     *
     * @return void
     * @throws SmartyException
     */
    public function testCreateWithXForwardedProto()
    {
        $view = ViewFactory::create(
            $this->container,
            $this->container->get('request')->withHeader('X-Forwarded-Proto', 'https')
        );
        $this->assertInstanceOf(Smarty::class, $view);
    }
}

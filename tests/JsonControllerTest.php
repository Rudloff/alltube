<?php

/**
 * JsonControllerTest class.
 */

namespace Alltube\Test;

use Alltube\Controller\JsonController;
use Exception;

/**
 * Unit tests for the FrontController class.
 */
class JsonControllerTest extends ControllerTest
{
    /**
     * Prepare tests.
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->controller = new JsonController($this->container);
    }

    /**
     * Test the json() function.
     *
     * @return void
     * @requires download
     */
    public function testJson()
    {
        $this->assertRequestIsOk('json', ['url' => 'https://www.youtube.com/watch?v=M7IpKCZ47pU']);
    }

    /**
     * Test the json() function with an error.
     *
     * @return void
     * @requires download
     */
    public function testJsonWithError()
    {
        $this->assertRequestIsServerError('json', ['url' => 'http://example.com/foo']);
    }

    /**
     * Test the json() function without the URL parameter.
     *
     * @return void
     */
    public function testJsonWithoutUrl()
    {
        $this->assertRequestIsClientError('json');
    }
}

<?php

/**
 * FrontControllerTest class.
 */

namespace Alltube\Test;

use Alltube\Controller\FrontController;
use Alltube\Exception\ConfigException;
use Alltube\Exception\DependencyException;
use Alltube\Library\Exception\AlltubeLibraryException;
use Exception;
use Graby\HttpClient\Plugin\ServerSideRequestForgeryProtection\Exception\InvalidURLException;
use Slim\Http\Environment;
use Slim\Http\Request;
use SmartyException;

/**
 * Unit tests for the FrontController class.
 */
class FrontControllerTest extends ControllerTest
{
    /**
     * Controller instance used in tests.
     * @var FrontController
     */
    protected $controller;

    /**
     * Prepare tests.
     * @throws ConfigException|SmartyException
     * @throws DependencyException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->controller = new FrontController($this->container);
    }

    /**
     * Test the constructor.
     *
     * @return void
     */
    public function testConstructor()
    {
        $this->assertInstanceOf(FrontController::class, new FrontController($this->container));
    }

    /**
     * Test the constructor with streams enabled.
     *
     * @return void
     */
    public function testConstructorWithStream()
    {
        $config = $this->container->get('config');
        $config->setOptions(['stream' => true]);
        $this->assertInstanceOf(FrontController::class, new FrontController($this->container));
    }

    /**
     * Test the index() function.
     *
     * @return void
     */
    public function testIndex()
    {
        $this->assertRequestIsOk('index');
    }

    /**
     * Test the index() function with a custom URI.
     *
     * @return void
     */
    public function testIndexWithCustomUri()
    {
        $result = $this->controller->index(
            Request::createFromEnvironment(
                Environment::mock(['REQUEST_URI' => '/foo', 'QUERY_STRING' => 'foo=bar'])
            ),
            $this->container->get('response')
        );
        $this->assertTrue($result->isOk());
    }

    /**
     * Test the extractors() function.
     *
     * @return void
     */
    public function testExtractors()
    {
        $this->assertRequestIsOk('extractors');
    }

    /**
     * Test the password() function.
     *
     * @return void
     */
    public function testPassword()
    {
        $this->assertRequestIsClientError('password');
    }

    /**
     * Test the info() function without the url parameter.
     *
     * @return void
     */
    public function testInfoWithoutUrl()
    {
        $this->expectException(InvalidURLException::class);
        $this->getRequestResult('info', []);
    }

    /**
     * Test the info() function.
     *
     * @return void
     * @requires download
     */
    public function testInfo()
    {
        $this->assertRequestIsOk('info', ['url' => 'https://www.youtube.com/watch?v=M7IpKCZ47pU']);
    }

    /**
     * Test the info() function with audio conversion.
     *
     * @return void
     * @requires download
     */
    public function testInfoWithAudio()
    {
        $config = $this->container->get('config');
        $config->setOptions(['convert' => true]);

        $this->assertRequestIsRedirect(
            'info',
            ['url' => 'https://www.youtube.com/watch?v=M7IpKCZ47pU', 'audio' => true]
        );
    }

    /**
     * Test the info() function with audio conversion from a Vimeo video.
     *
     * @return void
     * @requires download
     */
    public function testInfoWithVimeoAudio()
    {
        $config = $this->container->get('config');
        $config->setOptions(['convert' => true]);

        // So we can test the fallback to default format
        $this->assertRequestIsRedirect('info', ['url' => 'https://vimeo.com/251997032', 'audio' => true]);
    }

    /**
     * Test the info() function with audio enabled and an URL that doesn't need to be converted.
     *
     * @return void
     * @requires download
     */
    public function testInfoWithUnconvertedAudio()
    {
        $config = $this->container->get('config');
        $config->setOptions(['convert' => true]);

        $this->assertRequestIsRedirect(
            'info',
            [
                'url'   => 'https://2080.bandcamp.com/track/cygnus-x-the-orange-theme-2080-faulty-chip-cover',
                'audio' => true,
            ]
        );
    }

    /**
     * Test the info() function with a password.
     *
     * @return void
     * @requires download
     * @throws AlltubeLibraryException|InvalidURLException
     */
    public function testInfoWithPassword()
    {
        $result = $this->controller->info(
            $this->container->get('request')->withQueryParams(['url' => 'https://vimeo.com/68375962'])
                ->withParsedBody(['password' => 'youtube-dl']),
            $this->container->get('response')
        );
        $this->assertTrue($result->isOk());
    }

    /**
     * Test the info() function with a missing password.
     *
     * @return void
     * @requires download
     */
    public function testInfoWithMissingPassword()
    {
        $this->assertRequestIsClientError('info', ['url' => 'https://vimeo.com/68375962']);
        $this->assertRequestIsClientError('info', ['url' => 'https://vimeo.com/68375962', 'audio' => true]);
    }

    /**
     * Test the info() function with streams enabled.
     *
     * @return void
     * @requires download
     */
    public function testInfoWithStream()
    {
        $config = $this->container->get('config');
        $config->setOptions(['stream' => true]);

        $this->assertRequestIsOk('info', ['url' => 'https://www.youtube.com/watch?v=M7IpKCZ47pU']);
        $this->assertRequestIsOk(
            'info',
            ['url' => 'https://www.youtube.com/watch?v=M7IpKCZ47pU', 'audio' => true]
        );
    }

    /**
     * Test the info() function with a playlist.
     *
     * @return void
     * @requires download
     */
    public function testInfoWithPlaylist()
    {
        $this->assertRequestIsOk(
            'info',
            ['url' => 'https://www.youtube.com/playlist?list=PLgdySZU6KUXL_8Jq5aUkyNV7wCa-4wZsC']
        );
    }

    /**
     * Test the error() function.
     *
     * @return void
     */
    public function testError()
    {
        $result = $this->controller->error(
            $this->container->get('request'),
            $this->container->get('response'),
            new Exception('foo')
        );
        $this->assertTrue($result->isServerError());
    }

    /**
     * Test the locale() function.
     *
     * @return void
     */
    public function testLocale()
    {
        $this->assertTrue(
            $this->controller->locale(
                $this->container->get('request'),
                $this->container->get('response'),
                ['locale' => 'fr_FR']
            )->isRedirect()
        );
    }
}

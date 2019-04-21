<?php
/**
 * FrontControllerTest class.
 */

namespace Alltube\Test;

use Alltube\Config;
use Alltube\Controller\FrontController;
use Alltube\LocaleManager;
use Alltube\ViewFactory;
use Exception;
use Slim\Container;
use Slim\Http\Environment;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Unit tests for the FrontController class.
 */
class FrontControllerTest extends BaseTest
{
    /**
     * Slim dependency container.
     *
     * @var Container
     */
    private $container;

    /**
     * Mock HTTP request.
     *
     * @var Request
     */
    private $request;

    /**
     * Mock HTTP response.
     *
     * @var Response
     */
    private $response;

    /**
     * FrontController instance used in tests.
     *
     * @var FrontController
     */
    private $controller;

    /**
     * Config class instance.
     *
     * @var Config
     */
    private $config;

    /**
     * Prepare tests.
     */
    protected function setUp()
    {
        parent::setUp();

        $this->container = new Container();
        $this->request = Request::createFromEnvironment(Environment::mock());
        $this->response = new Response();
        $this->container['view'] = ViewFactory::create($this->container, $this->request);
        $this->container['locale'] = new LocaleManager();

        $this->controller = new FrontController($this->container);

        $this->container['router']->map(['GET'], '/', [$this->controller, 'index'])
            ->setName('index');
        $this->container['router']->map(['GET'], '/video', [$this->controller, 'video'])
            ->setName('video');
        $this->container['router']->map(['GET'], '/extractors', [$this->controller, 'extractors'])
            ->setName('extractors');
        $this->container['router']->map(['GET'], '/redirect', [$this->controller, 'redirect'])
            ->setName('redirect');
        $this->container['router']->map(['GET'], '/locale', [$this->controller, 'locale'])
            ->setName('locale');
    }

    /**
     * Run controller function with custom query parameters and return the result.
     *
     * @param string $request Controller function to call
     * @param array  $params  Query parameters
     *
     * @return Response HTTP response
     */
    private function getRequestResult($request, array $params)
    {
        return $this->controller->$request(
            $this->request->withQueryParams($params),
            $this->response
        );
    }

    /**
     * Assert that calling controller function with these parameters returns a 200 HTTP response.
     *
     * @param string $request Controller function to call
     * @param array  $params  Query parameters
     *
     * @return void
     */
    private function assertRequestIsOk($request, array $params = [])
    {
        $this->assertTrue($this->getRequestResult($request, $params)->isOk());
    }

    /**
     * Assert that calling controller function with these parameters returns an HTTP redirect.
     *
     * @param string $request Controller function to call
     * @param array  $params  Query parameters
     *
     * @return void
     */
    private function assertRequestIsRedirect($request, array $params = [])
    {
        $this->assertTrue($this->getRequestResult($request, $params)->isRedirect());
    }

    /**
     * Assert that calling controller function with these parameters returns an HTTP 500 error.
     *
     * @param string $request Controller function to call
     * @param array  $params  Query parameters
     *
     * @return void
     */
    private function assertRequestIsServerError($request, array $params = [])
    {
        $this->assertTrue($this->getRequestResult($request, $params)->isServerError());
    }

    /**
     * Assert that calling controller function with these parameters returns an HTTP 400 error.
     *
     * @param string $request Controller function to call
     * @param array  $params  Query parameters
     *
     * @return void
     */
    private function assertRequestIsClientError($request, array $params = [])
    {
        $this->assertTrue($this->getRequestResult($request, $params)->isClientError());
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
        Config::setOptions(['stream' => true]);
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
            $this->response
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
        $this->assertRequestIsOk('password');
    }

    /**
     * Test the video() function without the url parameter.
     *
     * @return void
     */
    public function testVideoWithoutUrl()
    {
        $this->assertRequestIsRedirect('video');
    }

    /**
     * Test the video() function.
     *
     * @return void
     */
    public function testVideo()
    {
        $this->assertRequestIsOk('video', ['url' => 'https://www.youtube.com/watch?v=M7IpKCZ47pU']);
    }

    /**
     * Test the video() function with audio conversion.
     *
     * @return void
     */
    public function testVideoWithAudio()
    {
        $this->assertRequestIsOk('video', ['url' => 'https://www.youtube.com/watch?v=M7IpKCZ47pU', 'audio' => true]);
    }

    /**
     * Test the video() function with audio conversion from a Vimeo video.
     *
     * @return void
     */
    public function testVideoWithVimeoAudio()
    {
        if (getenv('CI')) {
            $this->markTestSkipped('Travis is blacklisted by Vimeo.');
        }
        // So we can test the fallback to default format
        $this->assertRequestIsOk('video', ['url' => 'https://vimeo.com/251997032', 'audio' => true]);
    }

    /**
     * Test the video() function with audio enabled and an URL that doesn't need to be converted.
     *
     * @return void
     */
    public function testVideoWithUnconvertedAudio()
    {
        $this->assertRequestIsRedirect(
            'video',
            [
                'url'   => 'https://2080.bandcamp.com/track/cygnus-x-the-orange-theme-2080-faulty-chip-cover',
                'audio' => true,
            ]
        );
    }

    /**
     * Test the video() function with a password.
     *
     * @return void
     */
    public function testVideoWithPassword()
    {
        if (getenv('CI')) {
            $this->markTestSkipped('Travis is blacklisted by Vimeo.');
        }
        $result = $this->controller->video(
            $this->request->withQueryParams(['url' => 'http://vimeo.com/68375962'])
                ->withParsedBody(['password' => 'youtube-dl']),
            $this->response
        );
        $this->assertTrue($result->isOk());
    }

    /**
     * Test the video() function with a missing password.
     *
     * @return void
     */
    public function testVideoWithMissingPassword()
    {
        if (getenv('CI')) {
            $this->markTestSkipped('Travis is blacklisted by Vimeo.');
        }
        $this->assertRequestIsOk('video', ['url' => 'http://vimeo.com/68375962']);
        $this->assertRequestIsOk('video', ['url' => 'http://vimeo.com/68375962', 'audio' => true]);
    }

    /**
     * Test the video() function with streams enabled.
     *
     * @return void
     */
    public function testVideoWithStream()
    {
        Config::setOptions(['stream' => true]);

        $this->assertRequestIsOk('video', ['url' => 'https://www.youtube.com/watch?v=M7IpKCZ47pU']);
        $this->assertRequestIsOk(
            'video',
            ['url' => 'https://www.youtube.com/watch?v=M7IpKCZ47pU', 'audio' => true]
        );
    }

    /**
     * Test the video() function with a playlist.
     *
     * @return void
     */
    public function testVideoWithPlaylist()
    {
        $this->assertRequestIsOk(
            'video',
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
        $result = $this->controller->error($this->request, $this->response, new Exception('foo'));
        $this->assertTrue($result->isServerError());
    }

    /**
     * Test the redirect() function without the URL parameter.
     *
     * @return void
     */
    public function testRedirectWithoutUrl()
    {
        $this->assertRequestIsRedirect('redirect');
    }

    /**
     * Test the redirect() function.
     *
     * @return void
     */
    public function testRedirect()
    {
        $this->assertRequestIsRedirect('redirect', ['url' => 'https://www.youtube.com/watch?v=M7IpKCZ47pU']);
    }

    /**
     * Test the redirect() function with a specific format.
     *
     * @return void
     */
    public function testRedirectWithFormat()
    {
        $this->assertRequestIsRedirect(
            'redirect',
            ['url' => 'https://www.youtube.com/watch?v=M7IpKCZ47pU', 'format' => 'worst']
        );
    }

    /**
     * Test the redirect() function with streams enabled.
     *
     * @return void
     */
    public function testRedirectWithStream()
    {
        Config::setOptions(['stream' => true]);

        $this->assertRequestIsOk(
            'redirect',
            ['url' => 'https://www.youtube.com/watch?v=M7IpKCZ47pU']
        );
    }

    /**
     * Test the redirect() function with an M3U stream.
     *
     * @return void
     */
    public function testRedirectWithM3uStream()
    {
        if (getenv('CI')) {
            $this->markTestSkipped('Twitter returns a 429 error when the test is ran too many times.');
        }

        Config::setOptions(['stream' => true]);

        $this->assertRequestIsOk(
            'redirect',
            [
                'url'    => 'https://twitter.com/verge/status/813055465324056576/video/1',
                'format' => 'hls-2176',
            ]
        );
    }

    /**
     * Test the redirect() function with an RTMP stream.
     *
     * @return void
     */
    public function testRedirectWithRtmpStream()
    {
        $this->markTestIncomplete('We need to find another RTMP video.');

        Config::setOptions(['stream' => true]);

        $this->assertRequestIsOk(
            'redirect',
            ['url' => 'http://www.rtvnh.nl/video/131946', 'format' => 'rtmp-264']
        );
    }

    /**
     * Test the redirect() function with a remuxed video.
     *
     * @return void
     */
    public function testRedirectWithRemux()
    {
        Config::setOptions(['remux' => true]);

        $this->assertRequestIsOk(
            'redirect',
            [
                'url'    => 'https://www.youtube.com/watch?v=M7IpKCZ47pU',
                'format' => 'bestvideo+bestaudio',
            ]
        );
    }

    /**
     * Test the redirect() function with a remuxed video but remux disabled.
     *
     * @return void
     */
    public function testRedirectWithRemuxDisabled()
    {
        $this->assertRequestIsServerError(
            'redirect',
            [
                'url'    => 'https://www.youtube.com/watch?v=M7IpKCZ47pU',
                'format' => 'bestvideo+bestaudio',
            ]
        );
    }

    /**
     * Test the redirect() function with a missing password.
     *
     * @return void
     */
    public function testRedirectWithMissingPassword()
    {
        if (getenv('CI')) {
            $this->markTestSkipped('Travis is blacklisted by Vimeo.');
        }
        $this->assertRequestIsRedirect('redirect', ['url' => 'http://vimeo.com/68375962']);
    }

    /**
     * Test the redirect() function with an error.
     *
     * @return void
     */
    public function testRedirectWithError()
    {
        $this->assertRequestIsServerError('redirect', ['url' => 'http://example.com/foo']);
    }

    /**
     * Test the redirect() function with an video that returns an empty URL.
     * This can be caused by trying to redirect to a playlist.
     *
     * @return void
     */
    public function testRedirectWithEmptyUrl()
    {
        $this->assertRequestIsServerError(
            'redirect',
            ['url' => 'https://www.youtube.com/playlist?list=PLgdySZU6KUXL_8Jq5aUkyNV7wCa-4wZsC']
        );
    }

    /**
     * Test the redirect() function with a playlist stream.
     *
     * @return void
     * @requires OS Linux
     */
    public function testRedirectWithPlaylist()
    {
        Config::setOptions(['stream' => true]);

        $this->assertRequestIsOk(
            'redirect',
            ['url' => 'https://www.youtube.com/playlist?list=PLgdySZU6KUXL_8Jq5aUkyNV7wCa-4wZsC']
        );
    }

    /**
     * Test the redirect() function with an advanced conversion.
     *
     * @return void
     */
    public function testRedirectWithAdvancedConversion()
    {
        Config::setOptions(['convertAdvanced' => true]);

        $this->assertRequestIsOk(
            'redirect',
            [
                'url'           => 'https://www.youtube.com/watch?v=M7IpKCZ47pU',
                'format'        => 'best',
                'customConvert' => 'on',
                'customBitrate' => 32,
                'customFormat'  => 'flv',
            ]
        );
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

    /**
     * Test the json() function.
     *
     * @return void
     */
    public function testJson()
    {
        $this->assertRequestIsOk('json', ['url' => 'https://www.youtube.com/watch?v=M7IpKCZ47pU']);
    }

    /**
     * Test the json() function with an error.
     *
     * @return void
     */
    public function testJsonWithError()
    {
        $this->assertRequestIsServerError('json', ['url' => 'http://example.com/foo']);
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
                $this->request,
                $this->response,
                ['locale' => 'fr_FR']
            )->isRedirect()
        );
    }
}

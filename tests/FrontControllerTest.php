<?php
/**
 * FrontControllerTest class.
 */

namespace Alltube\Test;

use Alltube\Config;
use Alltube\Controller\FrontController;
use Alltube\LocaleManager;
use Alltube\ViewFactory;
use PHPUnit\Framework\TestCase;
use Slim\Container;
use Slim\Http\Environment;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Unit tests for the FrontController class.
 */
class FrontControllerTest extends TestCase
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
        $this->container = new Container();
        $this->request = Request::createFromEnvironment(Environment::mock());
        $this->response = new Response();
        $this->container['view'] = ViewFactory::create($this->container, $this->request);
        $this->container['locale'] = new LocaleManager();

        if (PHP_OS == 'WINNT') {
            $configFile = 'config_test_windows.yml';
        } else {
            $configFile = 'config_test.yml';
        }
        $this->config = Config::getInstance('config/'.$configFile);
        $this->controller = new FrontController($this->container, $this->config);

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
     * Destroy properties after test.
     */
    protected function tearDown()
    {
        Config::destroyInstance();
    }

    /**
     * Run controller function with custom query parameters and return the result.
     *
     * @param string $request Controller function to call
     * @param array  $params  Query parameters
     * @param Config $config  Custom config
     *
     * @return Response HTTP response
     */
    private function getRequestResult($request, array $params, Config $config = null)
    {
        if (isset($config)) {
            $controller = new FrontController($this->container, $config);
        } else {
            $controller = $this->controller;
        }

        return $controller->$request(
            $this->request->withQueryParams($params),
            $this->response
        );
    }

    /**
     * Assert that calling controller function with these parameters returns a 200 HTTP response.
     *
     * @param string $request Controller function to call
     * @param array  $params  Query parameters
     * @param Config $config  Custom config
     *
     * @return void
     */
    private function assertRequestIsOk($request, array $params = [], Config $config = null)
    {
        $this->assertTrue($this->getRequestResult($request, $params, $config)->isOk());
    }

    /**
     * Assert that calling controller function with these parameters returns an HTTP redirect.
     *
     * @param string $request Controller function to call
     * @param array  $params  Query parameters
     * @param Config $config  Custom config
     *
     * @return void
     */
    private function assertRequestIsRedirect($request, array $params = [], Config $config = null)
    {
        $this->assertTrue($this->getRequestResult($request, $params, $config)->isRedirect());
    }

    /**
     * Assert that calling controller function with these parameters returns an HTTP redirect.
     *
     * @param string $request Controller function to call
     * @param array  $params  Query parameters
     * @param Config $config  Custom config
     *
     * @return void
     */
    private function assertRequestIsServerError($request, array $params = [], Config $config = null)
    {
        $this->assertTrue($this->getRequestResult($request, $params, $config)->isServerError());
    }

    /**
     * Test the constructor.
     *
     * @return void
     */
    public function testConstructor()
    {
        $controller = new FrontController($this->container, $this->config);
        $this->assertInstanceOf(FrontController::class, $controller);
    }

    /**
     * Test the constructor with a default config.
     *
     * @return void
     * @requires OS Linux
     */
    public function testConstructorWithDefaultConfig()
    {
        $controller = new FrontController($this->container);
        $this->assertInstanceOf(FrontController::class, $controller);
    }

    /**
     * Test the constructor with streams enabled.
     *
     * @return void
     */
    public function testConstructorWithStream()
    {
        $this->config->stream = true;
        $controller = new FrontController($this->container, $this->config);
        $this->assertInstanceOf(FrontController::class, $controller);
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
        $this->config->stream = true;
        $this->assertRequestIsOk('video', ['url' => 'https://www.youtube.com/watch?v=M7IpKCZ47pU'], $this->config);
        $this->assertRequestIsOk(
            'video',
            ['url' => 'https://www.youtube.com/watch?v=M7IpKCZ47pU', 'audio' => true],
            $this->config
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
        $result = $this->controller->error($this->request, $this->response, new \Exception('foo'));
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
        $this->config->stream = true;
        $this->assertRequestIsOk(
            'redirect',
            ['url' => 'https://www.youtube.com/watch?v=M7IpKCZ47pU'],
            $this->config
        );
    }

    /**
     * Test the redirect() function with an M3U stream.
     *
     * @return void
     */
    public function testRedirectWithM3uStream()
    {
        $this->config->stream = true;
        $this->assertRequestIsOk(
            'redirect',
            [
                'url'    => 'https://twitter.com/verge/status/813055465324056576/video/1',
                'format' => 'hls-2176',
            ],
            $this->config
        );
    }

    /**
     * Test the redirect() function with an RTMP stream.
     *
     * @return void
     */
    public function testRedirectWithRtmpStream()
    {
        $this->config->stream = true;
        $this->assertRequestIsOk(
            'redirect',
            ['url' => 'http://www.canalc2.tv/video/12163', 'format' => 'rtmp'],
            $this->config
        );
    }

    /**
     * Test the redirect() function with a remuxed video.
     *
     * @return void
     */
    public function testRedirectWithRemux()
    {
        $this->config->remux = true;
        $this->assertRequestIsOk(
            'redirect',
            [
                'url'    => 'https://www.youtube.com/watch?v=M7IpKCZ47pU',
                'format' => 'bestvideo+bestaudio',
            ],
            $this->config
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
        $this->config->stream = true;
        $this->assertRequestIsOk(
            'redirect',
            ['url' => 'https://www.youtube.com/playlist?list=PLgdySZU6KUXL_8Jq5aUkyNV7wCa-4wZsC'],
            $this->config
        );
    }

    /**
     * Test the redirect() function with an advanced conversion.
     *
     * @return void
     */
    public function testRedirectWithAdvancedConversion()
    {
        $this->config->convertAdvanced = true;
        $this->assertRequestIsOk(
            'redirect',
            [
                'url'           => 'https://www.youtube.com/watch?v=M7IpKCZ47pU',
                'format'        => 'best',
                'customConvert' => 'on',
                'customBitrate' => 32,
                'customFormat'  => 'flv',
            ],
            $this->config
        );
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

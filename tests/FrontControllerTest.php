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
        $this->container['router']->map(['GET'], '/video', [$this->controller, 'info'])
            ->setName('info');
        $this->container['router']->map(['GET'], '/extractors', [$this->controller, 'extractors'])
            ->setName('extractors');
        $this->container['router']->map(['GET'], '/redirect', [$this->controller, 'download'])
            ->setName('download');
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
     * Test the info() function without the url parameter.
     *
     * @return void
     */
    public function testInfoWithoutUrl()
    {
        $this->assertRequestIsRedirect('info');
    }

    /**
     * Test the info() function.
     *
     * @return void
     */
    public function testInfo()
    {
        $this->assertRequestIsOk('info', ['url' => 'https://www.youtube.com/watch?v=M7IpKCZ47pU']);
    }

    /**
     * Test the info() function with audio conversion.
     *
     * @return void
     */
    public function testInfoWithAudio()
    {
        Config::setOptions(['convert' => true]);

        $this->assertRequestIsRedirect('info', ['url' => 'https://www.youtube.com/watch?v=M7IpKCZ47pU', 'audio' => true]);
    }

    /**
     * Test the info() function with audio conversion from a Vimeo video.
     *
     * @return void
     */
    public function testInfoWithVimeoAudio()
    {
        if (getenv('CI')) {
            $this->markTestSkipped('Travis is blacklisted by Vimeo.');
        }
        Config::setOptions(['convert' => true]);

        // So we can test the fallback to default format
        $this->assertRequestIsRedirect('info', ['url' => 'https://vimeo.com/251997032', 'audio' => true]);
    }

    /**
     * Test the info() function with audio enabled and an URL that doesn't need to be converted.
     *
     * @return void
     */
    public function testInfoWithUnconvertedAudio()
    {
        Config::setOptions(['convert' => true]);

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
     */
    public function testInfoWithPassword()
    {
        if (getenv('CI')) {
            $this->markTestSkipped('Travis is blacklisted by Vimeo.');
        }
        $result = $this->controller->info(
            $this->request->withQueryParams(['url' => 'http://vimeo.com/68375962'])
                ->withParsedBody(['password' => 'youtube-dl']),
            $this->response
        );
        $this->assertTrue($result->isOk());
    }

    /**
     * Test the info() function with a missing password.
     *
     * @return void
     */
    public function testInfoWithMissingPassword()
    {
        if (getenv('CI')) {
            $this->markTestSkipped('Travis is blacklisted by Vimeo.');
        }
        $this->assertRequestIsOk('info', ['url' => 'http://vimeo.com/68375962']);
        $this->assertRequestIsOk('info', ['url' => 'http://vimeo.com/68375962', 'audio' => true]);
    }

    /**
     * Test the info() function with streams enabled.
     *
     * @return void
     */
    public function testInfoWithStream()
    {
        Config::setOptions(['stream' => true]);

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
        $result = $this->controller->error($this->request, $this->response, new Exception('foo'));
        $this->assertTrue($result->isServerError());
    }

    /**
     * Test the download() function without the URL parameter.
     *
     * @return void
     */
    public function testDownloadWithoutUrl()
    {
        $this->assertRequestIsRedirect('download');
    }

    /**
     * Test the download() function.
     *
     * @return void
     */
    public function testDownload()
    {
        $this->assertRequestIsRedirect('download', ['url' => 'https://www.youtube.com/watch?v=M7IpKCZ47pU']);
    }

    /**
     * Test the download() function with a specific format.
     *
     * @return void
     */
    public function testDownloadWithFormat()
    {
        $this->assertRequestIsRedirect(
            'download',
            ['url' => 'https://www.youtube.com/watch?v=M7IpKCZ47pU', 'format' => 'worst']
        );
    }

    /**
     * Test the download() function with streams enabled.
     *
     * @return void
     */
    public function testDownloadWithStream()
    {
        Config::setOptions(['stream' => true]);

        $this->assertRequestIsOk(
            'download',
            ['url' => 'https://www.youtube.com/watch?v=M7IpKCZ47pU']
        );
    }

    /**
     * Test the download() function with an M3U stream.
     *
     * @return void
     */
    public function testDownloadWithM3uStream()
    {
        if (getenv('CI')) {
            $this->markTestSkipped('Twitter returns a 429 error when the test is ran too many times.');
        }

        Config::setOptions(['stream' => true]);

        $this->assertRequestIsOk(
            'download',
            [
                'url'    => 'https://twitter.com/verge/status/813055465324056576/video/1',
                'format' => 'hls-2176',
            ]
        );
    }

    /**
     * Test the download() function with an RTMP stream.
     *
     * @return void
     */
    public function testDownloadWithRtmpStream()
    {
        $this->markTestIncomplete('We need to find another RTMP video.');

        Config::setOptions(['stream' => true]);

        $this->assertRequestIsOk(
            'download',
            ['url' => 'http://www.rtvnh.nl/video/131946', 'format' => 'rtmp-264']
        );
    }

    /**
     * Test the download() function with a remuxed video.
     *
     * @return void
     */
    public function testDownloadWithRemux()
    {
        Config::setOptions(['remux' => true]);

        $this->assertRequestIsOk(
            'download',
            [
                'url'    => 'https://www.youtube.com/watch?v=M7IpKCZ47pU',
                'format' => 'bestvideo+bestaudio',
            ]
        );
    }

    /**
     * Test the download() function with a remuxed video but remux disabled.
     *
     * @return void
     */
    public function testDownloadWithRemuxDisabled()
    {
        $this->assertRequestIsServerError(
            'download',
            [
                'url'    => 'https://www.youtube.com/watch?v=M7IpKCZ47pU',
                'format' => 'bestvideo+bestaudio',
            ]
        );
    }

    /**
     * Test the download() function with a missing password.
     *
     * @return void
     */
    public function testDownloadWithMissingPassword()
    {
        if (getenv('CI')) {
            $this->markTestSkipped('Travis is blacklisted by Vimeo.');
        }
        $this->assertRequestIsRedirect('download', ['url' => 'http://vimeo.com/68375962']);
    }

    /**
     * Test the download() function with an error.
     *
     * @return void
     */
    public function testDownloadWithError()
    {
        $this->assertRequestIsServerError('download', ['url' => 'http://example.com/foo']);
    }

    /**
     * Test the download() function with an video that returns an empty URL.
     * This can be caused by trying to redirect to a playlist.
     *
     * @return void
     */
    public function testDownloadWithEmptyUrl()
    {
        $this->assertRequestIsServerError(
            'download',
            ['url' => 'https://www.youtube.com/playlist?list=PLgdySZU6KUXL_8Jq5aUkyNV7wCa-4wZsC']
        );
    }

    /**
     * Test the download() function with a playlist stream.
     *
     * @return void
     * @requires OS Linux
     */
    public function testDownloadWithPlaylist()
    {
        Config::setOptions(['stream' => true]);

        $this->assertRequestIsOk(
            'download',
            ['url' => 'https://www.youtube.com/playlist?list=PLgdySZU6KUXL_8Jq5aUkyNV7wCa-4wZsC']
        );
    }

    /**
     * Test the download() function with an advanced conversion.
     *
     * @return void
     */
    public function testDownloadWithAdvancedConversion()
    {
        Config::setOptions(['convertAdvanced' => true]);

        $this->assertRequestIsOk(
            'download',
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

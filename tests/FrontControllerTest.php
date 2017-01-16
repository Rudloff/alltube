<?php
/**
 * FrontControllerTest class.
 */

namespace Alltube\Test;

use Alltube\Config;
use Alltube\Controller\FrontController;
use Slim\Container;
use Slim\Http\Environment;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Unit tests for the FrontController class.
 */
class FrontControllerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Prepare tests.
     */
    protected function setUp()
    {
        $this->container = new Container();
        $this->request = Request::createFromEnvironment(Environment::mock());
        $this->response = new Response();
        $this->container['view'] = function ($c) {
            $view = new \Slim\Views\Smarty(__DIR__.'/../templates/');

            $smartyPlugins = new \Slim\Views\SmartyPlugins($c['router'], $this->request->getUri());
            $view->registerPlugin('function', 'path_for', [$smartyPlugins, 'pathFor']);
            $view->registerPlugin('function', 'base_url', [$smartyPlugins, 'baseUrl']);

            $view->registerPlugin('modifier', 'noscheme', 'Smarty_Modifier_noscheme');

            return $view;
        };
        $this->controller = new FrontController($this->container);
        $this->container['router']->map(['GET'], '/', [$this->controller, 'index'])
            ->setName('index');
        $this->container['router']->map(['GET'], '/video', [$this->controller, 'video'])
            ->setName('video');
        $this->container['router']->map(['GET'], '/extractors', [$this->controller, 'extractors'])
            ->setName('extractors');
        $this->container['router']->map(['GET'], '/redirect', [$this->controller, 'redirect'])
            ->setName('redirect');
    }

    /**
     * Destroy properties after test.
     */
    protected function tearDown()
    {
        Config::destroyInstance();
    }

    /**
     * Test the constructor with streams enabled.
     *
     * @return void
     */
    public function testConstructorWithStream()
    {
        $config = Config::getInstance();
        $config->stream = true;
        $controller = new FrontController($this->container);
        $this->assertInstanceOf(FrontController::class, $controller);
    }

    /**
     * Test the index() function.
     *
     * @return void
     */
    public function testIndex()
    {
        $result = $this->controller->index($this->request, $this->response);
        $this->assertTrue($result->isOk());
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
                Environment::mock(['REQUEST_URI'=>'/foo', 'QUERY_STRING'=>'foo=bar'])
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
        $result = $this->controller->extractors($this->request, $this->response);
        $this->assertTrue($result->isOk());
    }

    /**
     * Test the password() function.
     *
     * @return void
     */
    public function testPassword()
    {
        $result = $this->controller->password($this->request, $this->response);
        $this->assertTrue($result->isOk());
    }

    /**
     * Test the video() function without the url parameter.
     *
     * @return void
     */
    public function testVideoWithoutUrl()
    {
        $result = $this->controller->video($this->request, $this->response);
        $this->assertTrue($result->isRedirect());
    }

    /**
     * Test the video() function.
     *
     * @return void
     */
    public function testVideo()
    {
        $result = $this->controller->video(
            $this->request->withQueryParams(['url'=>'https://www.youtube.com/watch?v=M7IpKCZ47pU']),
            $this->response
        );
        $this->assertTrue($result->isOk());
    }

    /**
     * Test the video() function with audio conversion.
     *
     * @return void
     */
    public function testVideoWithAudio()
    {
        $result = $this->controller->video(
            $this->request->withQueryParams(['url'=>'https://www.youtube.com/watch?v=M7IpKCZ47pU', 'audio'=>true]),
            $this->response
        );
        $this->assertTrue($result->isOk());
    }

    /**
     * Test the video() function with audio enabled and an URL that doesn't need to be converted.
     *
     * @return void
     */
    public function testVideoWithUnconvertedAudio()
    {
        $result = $this->controller->video(
            $this->request->withQueryParams(
                ['url' => 'https://soundcloud.com/verwandlungskuenstler/metamorphosis-by-franz-kafka-1',
                'audio'=> true, ]
            ),
            $this->response
        );
        $this->assertTrue($result->isRedirect());
    }

    /**
     * Test the video() function with a password.
     *
     * @return void
     */
    public function testVideoWithPassword()
    {
        $result = $this->controller->video(
            $this->request->withQueryParams(['url'=>'http://vimeo.com/68375962'])
                ->withParsedBody(['password'=>'youtube-dl']),
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
        $result = $this->controller->video(
            $this->request->withQueryParams(['url'=>'http://vimeo.com/68375962']),
            $this->response
        );
        $this->assertTrue($result->isOk());
        $result = $this->controller->video(
            $this->request->withQueryParams(['url'=>'http://vimeo.com/68375962', 'audio'=>true]),
            $this->response
        );
        $this->assertTrue($result->isOk());
    }

    /**
     * Test the video() function with streams enabled.
     *
     * @return void
     */
    public function testVideoWithStream()
    {
        $config = Config::getInstance();
        $config->stream = true;
        $result = $this->controller->video(
            $this->request->withQueryParams(['url'=>'https://www.youtube.com/watch?v=M7IpKCZ47pU']),
            $this->response
        );
        $this->assertTrue($result->isOk());
        $result = $this->controller->video(
            $this->request->withQueryParams(['url'=>'https://www.youtube.com/watch?v=M7IpKCZ47pU', 'audio'=>true]),
            $this->response
        );
        $this->assertTrue($result->isOk());
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
        $result = $this->controller->redirect($this->request, $this->response);
        $this->assertTrue($result->isRedirect());
    }

    /**
     * Test the redirect() function.
     *
     * @return void
     */
    public function testRedirect()
    {
        $result = $this->controller->redirect(
            $this->request->withQueryParams(['url'=>'https://www.youtube.com/watch?v=M7IpKCZ47pU']),
            $this->response
        );
        $this->assertTrue($result->isRedirect());
    }

    /**
     * Test the redirect() function with streams enabled.
     *
     * @return void
     */
    public function testRedirectWithStream()
    {
        $config = Config::getInstance();
        $config->stream = true;
        $result = $this->controller->redirect(
            $this->request->withQueryParams(['url'=>'https://www.youtube.com/watch?v=M7IpKCZ47pU']),
            $this->response
        );
        $this->assertTrue($result->isOk());
    }

    /**
     * Test the redirect() function with an M3U stream.
     *
     * @return void
     */
    public function testRedirectWithM3uStream()
    {
        $config = Config::getInstance();
        $config->stream = true;
        $result = $this->controller->redirect(
            $this->request->withQueryParams(['url'=>'https://twitter.com/verge/status/813055465324056576/video/1']),
            $this->response
        );
        $this->assertTrue($result->isOk());
    }

    /**
     * Test the redirect() function with a missing password.
     *
     * @return void
     */
    public function testRedirectWithMissingPassword()
    {
        $result = $this->controller->redirect(
            $this->request->withQueryParams(['url'=>'http://vimeo.com/68375962']),
            $this->response
        );
        $this->assertTrue($result->isRedirect());
    }

    /**
     * Test the redirect() function with an error.
     *
     * @return void
     */
    public function testRedirectWithError()
    {
        $result = $this->controller->redirect(
            $this->request->withQueryParams(['url'=>'http://example.com/foo']),
            $this->response
        );
        $this->assertTrue($result->isServerError());
    }
}

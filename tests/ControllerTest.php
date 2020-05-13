<?php

/**
 * ControllerTest class.
 */

namespace Alltube\Test;

use Alltube\Controller\BaseController;
use Alltube\Controller\DownloadController;
use Alltube\Controller\FrontController;
use Alltube\LocaleManager;
use Alltube\ViewFactory;
use Exception;
use Slim\Container;
use Slim\Http\Environment;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Abstract class used by the controller tests.
 */
abstract class ControllerTest extends BaseTest
{
    /**
     * Slim dependency container.
     *
     * @var Container
     */
    protected $container;

    /**
     * Mock HTTP request.
     *
     * @var Request
     */
    protected $request;

    /**
     * Mock HTTP response.
     *
     * @var Response
     */
    protected $response;

    /**
     * Controller instance used in tests.
     * @var BaseController
     */
    protected $controller;

    /**
     * Prepare tests.
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->container = new Container();
        $this->request = Request::createFromEnvironment(Environment::mock());
        $this->response = new Response();
        $this->container['locale'] = LocaleManager::getInstance();
        $this->container['view'] = ViewFactory::create($this->container, $this->request);

        $frontController = new FrontController($this->container);
        $downloadController = new DownloadController($this->container);

        $this->container['router']->map(['GET'], '/', [$frontController, 'index'])
            ->setName('index');
        $this->container['router']->map(['GET'], '/video', [$frontController, 'info'])
            ->setName('info');
        $this->container['router']->map(['GET'], '/extractors', [$frontController, 'extractors'])
            ->setName('extractors');
        $this->container['router']->map(['GET'], '/locale', [$frontController, 'locale'])
            ->setName('locale');
        $this->container['router']->map(['GET'], '/redirect', [$downloadController, 'download'])
            ->setName('download');
    }

    /**
     * Run controller function with custom query parameters and return the result.
     *
     * @param string $request Controller function to call
     * @param mixed[] $params Query parameters
     *
     * @return Response HTTP response
     */
    protected function getRequestResult($request, array $params)
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
     * @param mixed[] $params Query parameters
     *
     * @return void
     */
    protected function assertRequestIsOk($request, array $params = [])
    {
        $this->assertTrue($this->getRequestResult($request, $params)->isOk());
    }

    /**
     * Assert that calling controller function with these parameters returns an HTTP redirect.
     *
     * @param string $request Controller function to call
     * @param mixed[] $params Query parameters
     *
     * @return void
     */
    protected function assertRequestIsRedirect($request, array $params = [])
    {
        $this->assertTrue($this->getRequestResult($request, $params)->isRedirect());
    }

    /**
     * Assert that calling controller function with these parameters returns an HTTP 500 error.
     *
     * @param string $request Controller function to call
     * @param mixed[] $params Query parameters
     *
     * @return void
     */
    protected function assertRequestIsServerError($request, array $params = [])
    {
        $this->assertTrue($this->getRequestResult($request, $params)->isServerError());
    }

    /**
     * Assert that calling controller function with these parameters returns an HTTP 400 error.
     *
     * @param string $request Controller function to call
     * @param mixed[] $params Query parameters
     *
     * @return void
     */
    protected function assertRequestIsClientError($request, array $params = [])
    {
        $this->assertTrue($this->getRequestResult($request, $params)->isClientError());
    }
}

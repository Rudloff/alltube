<?php

/**
 * ControllerTest class.
 */

namespace Alltube\Test;

use Alltube\Locale;
use Alltube\Controller\BaseController;
use Alltube\Controller\DownloadController;
use Alltube\Controller\FrontController;
use Alltube\Exception\ConfigException;
use Alltube\Exception\DependencyException;
use Slim\Http\Response;
use Slim\Views\Smarty;
use SmartyException;

/**
 * Abstract class used by the controller tests.
 */
abstract class ControllerTest extends ContainerTest
{
    /**
     * Controller instance used in tests.
     * @var BaseController
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

        $this->container->get('locale')->setLocale(new Locale('en_US'));

        $frontController = new FrontController($this->container);
        $downloadController = new DownloadController($this->container);

        $router = $this->container->get('router');

        $router->map(['GET'], '/', [$frontController, 'index'])
            ->setName('index');
        $router->map(['GET'], '/video', [$frontController, 'info'])
            ->setName('info');
        $router->map(['GET'], '/extractors', [$frontController, 'extractors'])
            ->setName('extractors');
        $router->map(['GET'], '/locale', [$frontController, 'locale'])
            ->setName('locale');
        $router->map(['GET'], '/redirect', [$downloadController, 'download'])
            ->setName('download');

        /** @var Smarty $view */
        $view = $this->container->get('view');

        // Make sure we start the tests without compiled templates.
        $view->getSmarty()->clearCompiledTemplate();
    }

    /**
     * Run controller function with custom query parameters and return the result.
     *
     * @param string $request Controller function to call
     * @param mixed[] $params Query parameters
     *
     * @return Response HTTP response
     */
    protected function getRequestResult(string $request, array $params): Response
    {
        return $this->controller->$request(
            $this->container->get('request')->withQueryParams($params),
            $this->container->get('response')
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
    protected function assertRequestIsOk(string $request, array $params = [])
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
    protected function assertRequestIsRedirect(string $request, array $params = [])
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
    protected function assertRequestIsServerError(string $request, array $params = [])
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
    protected function assertRequestIsClientError(string $request, array $params = [])
    {
        $this->assertTrue($this->getRequestResult($request, $params)->isClientError());
    }
}

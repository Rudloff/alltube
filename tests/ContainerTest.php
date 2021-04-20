<?php

/**
 * PlaylistArchiveStreamTest class.
 */

namespace Alltube\Test;

use Alltube\Config;
use Alltube\Exception\ConfigException;
use Alltube\Exception\DependencyException;
use Alltube\Factory\LocaleManagerFactory;
use Alltube\Factory\SessionFactory;
use Alltube\Factory\ViewFactory;
use Psr\Log\NullLogger;
use Slim\Container;
use Slim\Http\Environment;
use SmartyException;

/**
 * Base class for tests that require a container.
 */
abstract class ContainerTest extends BaseTest
{
    /**
     * Slim dependency container.
     *
     * @var Container
     */
    protected $container;

    /**
     * Prepare tests.
     * @throws ConfigException
     * @throws DependencyException
     * @throws SmartyException
     */
    protected function setUp(): void
    {
        $this->checkRequirements();

        $this->container = new Container(['environment' => Environment::mock()]);
        $this->container['root_path'] = dirname(__DIR__);
        $this->container['config'] = Config::fromFile($this->getConfigFile());
        $this->container['session'] = SessionFactory::create($this->container);
        $this->container['locale'] = LocaleManagerFactory::create($this->container);
        $this->container['view'] = ViewFactory::create($this->container);
        $this->container['logger'] = new NullLogger();
    }

    /**
     * Cleanup after each test.
     */
    protected function tearDown(): void
    {
        $this->container->get('session')->clear();
    }
}

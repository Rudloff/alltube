<?php

/**
 * LocaleManagerTest class.
 */

namespace Alltube\Test;

use Alltube\Exception\ConfigException;
use Alltube\Exception\DependencyException;
use Alltube\Factory\SessionFactory;
use Alltube\Locale;
use Alltube\LocaleManager;
use SmartyException;

/**
 * Unit tests for the LocaleManagerTest class.
 */
class LocaleManagerTest extends ContainerTest
{
    /**
     * LocaleManager class instance.
     *
     * @var LocaleManager
     */
    private $localeManager;

    /**
     * Prepare tests.
     *
     * @throws ConfigException
     * @throws DependencyException
     * @throws SmartyException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $_SESSION[LocaleManager::class]['locale'] = 'foo_BAR';
        $this->localeManager = new LocaleManager(SessionFactory::create($this->container));
    }

    /**
     * Unset locale after each test.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        $this->localeManager->unsetLocale();
    }

    /**
     * Test the getSupportedLocales function.
     *
     * @return void
     */
    public function testGetSupportedLocales()
    {
        foreach ($this->localeManager->getSupportedLocales() as $locale) {
            $this->assertInstanceOf(Locale::class, $locale);
        }
    }

    /**
     * Test the getLocale function.
     *
     * @return void
     */
    public function testGetLocale()
    {
        $this->assertEquals(new Locale('foo_BAR'), $this->localeManager->getLocale());
    }

    /**
     * Test the setLocale function.
     *
     * @return void
     */
    public function testSetLocale()
    {
        $this->localeManager->setLocale(new Locale('foo_BAR'));
        $locale = $this->localeManager->getLocale();
        $this->assertInstanceOf(Locale::class, $locale);
        $this->assertEquals('foo_BAR', (string) $locale);
    }

    /**
     * Test the unsetLocale function.
     *
     * @return void
     */
    public function testUnsetLocale()
    {
        $this->localeManager->unsetLocale();
        $this->assertNull($this->localeManager->getLocale());
    }

    /**
     * Test that the environment is correctly set up.
     *
     * @return void
     */
    public function testEnv()
    {
        putenv('LANG=foo_BAR');
        $this->localeManager->setLocale(new Locale('foo_BAR'));
        $this->assertEquals('foo_BAR', getenv('LANG'));
    }
}

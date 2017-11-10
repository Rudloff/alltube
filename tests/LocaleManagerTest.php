<?php
/**
 * LocaleManagerTest class.
 */

namespace Alltube\Test;

use Alltube\Locale;
use Alltube\LocaleManager;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for the Config class.
 */
class LocaleManagerTest extends TestCase
{
    /**
     * LocaleManager class instance.
     *
     * @var LocaleManager
     */
    private $localeManager;

    /**
     * Prepare tests.
     */
    protected function setUp()
    {
        $this->localeManager = new LocaleManager();
    }

    /**
     * Test the getSupportedLocales function.
     *
     * @return void
     */
    public function testConstructorWithCookies()
    {
        $_SESSION['Alltube\LocaleManager']['locale'] = 'foo_BAR';
        $localeManager = new LocaleManager([]);
        $this->assertEquals('foo_BAR', (string) $localeManager->getLocale());
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
}

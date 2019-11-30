<?php

/**
 * LocaleTest class.
 */

namespace Alltube\Test;

use Alltube\Locale;

/**
 * Unit tests for the LocaleTest class.
 */
class LocaleTest extends BaseTest
{
    /**
     * Locale class instance.
     *
     * @var Locale
     */
    private $localeObject;

    /**
     * Prepare tests.
     */
    protected function setUp(): void
    {
        $this->localeObject = new Locale('fr_FR');
    }

    /**
     * Test the __toString function.
     *
     * @return void
     */
    public function testGetToString()
    {
        $this->assertEquals('fr_FR', $this->localeObject->__toString());
    }

    /**
     * Test the getFullName function.
     *
     * @return void
     */
    public function testGetFullName()
    {
        $this->assertEquals('français (France)', $this->localeObject->getFullName());
    }

    /**
     * Test the getIso15897 function.
     *
     * @return void
     */
    public function testGetIso15897()
    {
        $this->assertEquals('fr_FR', $this->localeObject->getIso15897());
    }

    /**
     * Test the getBcp47 function.
     *
     * @return void
     */
    public function testGetBcp47()
    {
        $this->assertEquals('fr-FR', $this->localeObject->getBcp47());
    }

    /**
     * Test the getIso3166 function.
     *
     * @return void
     */
    public function testGetIso3166()
    {
        $this->assertEquals('fr', $this->localeObject->getIso3166());
    }

    /**
     * Test the getCountry function.
     *
     * @return void
     */
    public function testGetCountry()
    {
        $this->assertEquals(country('fr'), $this->localeObject->getCountry());
    }
}

<?php
/**
 * LocaleTest class.
 */

namespace Alltube\Test;

use Alltube\Locale;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for the Config class.
 */
class LocaleTest extends TestCase
{
    /**
     * Locale class instance.
     *
     * @var Locale
     */
    private $locale;

    /**
     * Prepare tests.
     */
    protected function setUp()
    {
        $this->locale = new Locale('fr_FR');
    }

    /**
     * Test the __toString function.
     *
     * @return void
     */
    public function testGetToString()
    {
        $this->assertEquals('fr_FR', $this->locale->__toString());
    }

    /**
     * Test the getFullName function.
     *
     * @return void
     */
    public function testGetFullName()
    {
        $this->assertEquals('français (France)', $this->locale->getFullName());
    }

    /**
     * Test the getIso15897 function.
     *
     * @return void
     */
    public function testGetIso15897()
    {
        $this->assertEquals('fr_FR', $this->locale->getIso15897());
    }

    /**
     * Test the getBcp47 function.
     *
     * @return void
     */
    public function testGetBcp47()
    {
        $this->assertEquals('fr-FR', $this->locale->getBcp47());
    }

    /**
     * Test the getIso3166 function.
     *
     * @return void
     */
    public function testGetIso3166()
    {
        $this->assertEquals('fr', $this->locale->getIso3166());
    }
}

<?php
/**
 * Locale class.
 */

namespace Alltube;

use Teto\HTTP\AcceptLanguage;

/**
 * Class used to represent locales.
 */
class Locale
{
    /**
     * Locale language.
     *
     * @var string
     */
    private $language;

    /**
     * Locale region.
     *
     * @var string
     */
    private $region;

    /**
     * Locale constructor.
     *
     * @param string $locale ISO 15897 code
     */
    public function __construct($locale)
    {
        $parse = AcceptLanguage::parse($locale);
        $this->language = $parse[1]['language'];
        $this->region = $parse[1]['region'];
    }

    /**
     * Convert the locale to a string.
     *
     * @return string ISO 15897 code
     */
    public function __toString()
    {
        return $this->getIso15897();
    }

    /**
     * Get the full name of the locale.
     *
     * @return string
     */
    public function getFullName()
    {
        return \Locale::getDisplayName($this->getIso15897(), $this->getIso15897());
    }

    /**
     * Get the ISO 15897 code.
     *
     * @return string
     */
    public function getIso15897()
    {
        return $this->language.'_'.$this->region;
    }

    /**
     * Get the BCP 47 code.
     *
     * @return string
     */
    public function getBcp47()
    {
        return $this->language.'-'.$this->region;
    }

    /**
     * Get the ISO 3166 code.
     *
     * @return string
     */
    public function getIso3166()
    {
        return strtolower($this->region);
    }

    /**
     * Get country information from locale.
     *
     * @return \Rinvex\Country\Country|array
     */
    public function getCountry()
    {
        return country($this->getIso3166());
    }
}

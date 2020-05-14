<?php

/**
 * Locale class.
 */

namespace Alltube;

use Locale as PHPLocale;
use Rinvex\Country\Country;
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
        if (!empty($parse[1]['region'])) {
            $this->region = $parse[1]['region'];
        }
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
        return PHPLocale::getDisplayName($this->getIso15897(), $this->getIso15897());
    }

    /**
     * Get the ISO 15897 code.
     *
     * @return string
     */
    public function getIso15897()
    {
        if (isset($this->region)) {
            return $this->language . '_' . $this->region;
        } else {
            return $this->language;
        }
    }

    /**
     * Get the BCP 47 code.
     *
     * @return string
     */
    public function getBcp47()
    {
        if (isset($this->region)) {
            return $this->language . '-' . $this->region;
        } else {
            return $this->language;
        }
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
     * @return Country|Country[]|null
     */
    public function getCountry()
    {
        if (isset($this->region)) {
            return country($this->getIso3166());
        }

        return null;
    }
}

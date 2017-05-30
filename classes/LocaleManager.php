<?php
/**
 * LocaleManager class.
 */

namespace Alltube;

/**
 * Class used to manage locales.
 */
class LocaleManager
{
    /**
     * Supported locales.
     *
     * @var array
     */
    private $supportedLocales = ['en_US', 'fr_FR', 'zh_CN'];

    /**
     * Current locale.
     *
     * @var string
     */
    private $curLocale;

    /**
     * LocaleManager constructor.
     *
     * @param array $cookies Cookie array
     */
    public function __construct(array $cookies = [])
    {
        $session_factory = new \Aura\Session\SessionFactory();
        $session = $session_factory->newInstance($cookies);
        $this->sessionSegment = $session->getSegment('Alltube\LocaleManager');
        $this->setLocale($this->sessionSegment->get('locale'));
    }

    /**
     * Get a list of supported locales.
     *
     * @return array
     */
    public function getSupportedLocales()
    {
        $return = [];
        foreach ($this->supportedLocales as $supportedLocale) {
            $return[$supportedLocale] = \Locale::getDisplayName($supportedLocale, $this->curLocale);
        }

        return $return;
    }

    /**
     * Get the current locale.
     *
     * @return string
     */
    public function getLocale()
    {
        return $this->curLocale;
    }

    /**
     * Set the current locale.
     *
     * @param string $locale Locale code.
     */
    public function setLocale($locale)
    {
        putenv('LANG='.$locale);
        setlocale(LC_ALL, [$locale, $locale.'.utf8']);
        $this->curLocale = $locale;
        $this->sessionSegment->set('locale', $locale);
    }
}

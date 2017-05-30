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
     * @var Locale
     */
    private $curLocale;

    /**
     * Session segment used to store session variables.
     *
     * @var \Aura\Session\Segment
     */
    private $sessionSegment;

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
        $cookieLocale = $this->sessionSegment->get('locale');
        if (isset($cookieLocale)) {
            $this->setLocale(new Locale($this->sessionSegment->get('locale')));
        }
    }

    /**
     * Get a list of supported locales.
     *
     * @return Locale[]
     */
    public function getSupportedLocales()
    {
        $return = [];
        foreach ($this->supportedLocales as $supportedLocale) {
            $return[] = new Locale($supportedLocale);
        }

        return $return;
    }

    /**
     * Get the current locale.
     *
     * @return Locale
     */
    public function getLocale()
    {
        return $this->curLocale;
    }

    /**
     * Set the current locale.
     *
     * @param Locale $locale Locale
     */
    public function setLocale(Locale $locale)
    {
        putenv('LANG='.$locale);
        setlocale(LC_ALL, [$locale, $locale.'.utf8']);
        $this->curLocale = $locale;
        $this->sessionSegment->set('locale', $locale);
    }
}

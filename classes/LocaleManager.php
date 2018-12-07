<?php
/**
 * LocaleManager class.
 */

namespace Alltube;

use Aura\Session\Segment;
use Aura\Session\SessionFactory;
use Symfony\Component\Process\Process;

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
    private $supportedLocales = ['en_US', 'fr_FR', 'zh_CN', 'es_ES', 'pt_BR', 'de_DE', 'ar_001'];

    /**
     * Current locale.
     *
     * @var Locale
     */
    private $curLocale;

    /**
     * Session segment used to store session variables.
     *
     * @var Segment
     */
    private $sessionSegment;

    /**
     * LocaleManager constructor.
     *
     * @param array $cookies Cookie array
     */
    public function __construct(array $cookies = [])
    {
        $session_factory = new SessionFactory();
        $session = $session_factory->newInstance($cookies);
        $this->sessionSegment = $session->getSegment(self::class);
        $cookieLocale = $this->sessionSegment->get('locale');
        if (isset($cookieLocale)) {
            $this->setLocale(new Locale($cookieLocale));
        }
        bindtextdomain('Alltube', __DIR__.'/../i18n/');
        textdomain('Alltube');
    }

    /**
     * Get a list of supported locales.
     *
     * @return Locale[]
     */
    public function getSupportedLocales()
    {
        $return = [];
        $process = new Process(['locale', '-a']);
        $process->run();
        $installedLocales = explode(PHP_EOL, trim($process->getOutput()));
        foreach ($this->supportedLocales as $supportedLocale) {
            if (in_array($supportedLocale, $installedLocales)
                || in_array($supportedLocale.'.utf8', $installedLocales)
            ) {
                $return[] = new Locale($supportedLocale);
            }
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
        setlocale(LC_ALL, [$locale.'.utf8', $locale]);
        $this->curLocale = $locale;
        $this->sessionSegment->set('locale', $locale);
    }

    /**
     * Unset the current locale.
     */
    public function unsetLocale()
    {
        $this->curLocale = null;
        $this->sessionSegment->clear();
    }
}

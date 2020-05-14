<?php

/**
 * LocaleManager class.
 */

namespace Alltube;

use Aura\Session\Segment;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Translation\Loader\PoFileLoader;

/**
 * Class used to manage locales.
 */
class LocaleManager
{
    /**
     * Supported locales.
     *
     * @var string[]
     */
    private $supportedLocales = ['en_US', 'fr_FR', 'zh_CN', 'es_ES', 'pt_BR', 'de_DE', 'ar', 'pl_PL', 'tr_TR'];

    /**
     * Current locale.
     *
     * @var Locale|null
     */
    private $curLocale;

    /**
     * Session segment used to store session variables.
     *
     * @var Segment
     */
    private $sessionSegment;

    /**
     * Default locale.
     *
     * @var string
     */
    private const DEFAULT_LOCALE = 'en';

    /**
     * Symfony Translator instance.
     *
     * @var Translator
     */
    private $translator;

    /**
     * Singleton instance.
     *
     * @var LocaleManager|null
     */
    private static $instance;

    /**
     * LocaleManager constructor.
     */
    private function __construct()
    {
        $session = SessionManager::getSession();
        $this->sessionSegment = $session->getSegment(self::class);
        $cookieLocale = $this->sessionSegment->get('locale');

        $this->translator = new Translator(self::DEFAULT_LOCALE);
        if (isset($cookieLocale)) {
            $this->setLocale(new Locale($cookieLocale));
        }

        $this->translator->addLoader('gettext', new PoFileLoader());
        foreach ($this->getSupportedLocales() as $locale) {
            $this->translator->addResource(
                'gettext',
                __DIR__ . '/../i18n/' . $locale->getIso15897() . '/LC_MESSAGES/Alltube.po',
                $locale->getIso15897()
            );
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
     * @return Locale|null
     */
    public function getLocale()
    {
        return $this->curLocale;
    }

    /**
     * Set the current locale.
     *
     * @param Locale $locale Locale
     * @return void
     */
    public function setLocale(Locale $locale)
    {
        $this->translator->setLocale($locale->getIso15897());
        $this->curLocale = $locale;
        $this->sessionSegment->set('locale', $locale);
    }

    /**
     * Unset the current locale.
     * @return void
     */
    public function unsetLocale()
    {
        $this->translator->setLocale(self::DEFAULT_LOCALE);
        $this->curLocale = null;
        $this->sessionSegment->clear();
    }

    /**
     * Smarty "t" block.
     *
     * @param mixed[] $params Block parameters
     * @param string $text Block content
     *
     * @return string Translated string
     */
    public function smartyTranslate(array $params, $text)
    {
        if (isset($params['params'])) {
            return $this->t($text, $params['params']);
        } else {
            return $this->t($text);
        }
    }

    /**
     * Translate a string.
     *
     * @param string $string String to translate
     *
     * @param mixed[] $params
     * @return string Translated string
     */
    public function t($string, array $params = [])
    {
        return $this->translator->trans($string, $params);
    }

    /**
     * Get LocaleManager singleton instance.
     *
     * @return LocaleManager
     */
    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Destroy singleton instance.
     *
     * @return void
     */
    public static function destroyInstance()
    {
        self::$instance = null;
    }
}

<?php

/**
 * LocaleManager class.
 */

namespace Alltube;

use Aura\Session\Segment;
use Aura\Session\Session;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Translation\Loader\PoFileLoader;

/**
 * Class used to manage locales.
 */
class LocaleManager
{
    /**
     * Path to locales.
     */
    private const PATH = __DIR__ . '/../i18n/';

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
     * LocaleManager constructor.
     * @param Session $session
     */
    public function __construct(Session $session)
    {
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
                self::PATH . $locale->getIso15897() . '/LC_MESSAGES/Alltube.po',
                $locale->getIso15897()
            );
        }
    }

    /**
     * Get a list of supported locales.
     *
     * @return Locale[]
     */
    public function getSupportedLocales(): array
    {
        $return = [
            new Locale('en_US')
        ];

        $finder = new Finder();
        $finder->depth(0)
            ->directories()
            ->in(self::PATH);

        foreach ($finder as $file) {
            $return[] = new Locale($file->getFilename());
        }

        return $return;
    }

    /**
     * Get the current locale.
     *
     * @return Locale|null
     */
    public function getLocale(): ?Locale
    {
        return $this->curLocale;
    }

    /**
     * Set the current locale.
     *
     * @param Locale $locale Locale
     * @return void
     */
    public function setLocale(Locale $locale): void
    {
        $this->translator->setLocale($locale->getIso15897());
        $this->curLocale = $locale;
        $this->sessionSegment->set('locale', $locale);
    }

    /**
     * Unset the current locale.
     * @return void
     */
    public function unsetLocale(): void
    {
        $this->translator->setLocale(self::DEFAULT_LOCALE);
        $this->curLocale = null;
        $this->sessionSegment->clear();
    }

    /**
     * Smarty "t" block.
     *
     * @param string[]|string[][] $params Block parameters
     * @param string|null $text Block content
     *
     * @return string Translated string
     */
    public function smartyTranslate(array $params, string $text = null): string
    {
        if (isset($params['params']) && is_array($params['params'])) {
            return $this->t($text, $params['params']);
        } else {
            return $this->t($text);
        }
    }

    /**
     * Translate a string.
     *
     * @param string|null $string $string String to translate
     *
     * @param string[] $params
     * @return string Translated string
     */
    public function t(string $string = null, array $params = []): string
    {
        if (isset($string)) {
            return $this->translator->trans($string, $params);
        }

        return '';
    }
}
